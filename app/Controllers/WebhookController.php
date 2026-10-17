<?php
declare (strict_types = 1);

namespace App\Controllers;

use App\Events\PaymentFailedEvent;
use App\Events\PaymentSucceededEvent;
use JsonException;
use Rhapsody\Core\Controllers\BaseWebhookController;
use Rhapsody\Core\Events\EventDispatcher;
use Rhapsody\Core\Request;
use Rhapsody\Core\Response;
use Rhapsody\Core\Support\Arr;
use Rhapsody\Core\Support\Env;
use Rhapsody\Core\Support\Json;
use RuntimeException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\WebhookSignature;
use Throwable;

class WebhookController extends BaseWebhookController
{
    /**
     * Maximum age of a signed webhook, in seconds (replay protection).
     */
    private const int SIGNATURE_TOLERANCE = 300;

    public function __construct(
        protected EventDispatcher $dispatcher
    ) {}

    public function handle(Request $request): Response
    {
        // If you use a different gateway, adapt this section accordingly.
        $webhookSecret = Env::string('STRIPE_WEBHOOK_SECRET');
        if ($webhookSecret === '') {
            error_log('[Webhook] Missing STRIPE_WEBHOOK_SECRET in environment.');
            return $this->json(['error' => 'Webhook secret not configured'], 500);
        }

        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        if (! is_string($signature) || $signature === '') {
            return $this->json(['error' => 'Webhook verification failed'], 400);
        }

        // 1. Verify the signature before trusting anything in the payload.
        try {
            WebhookSignature::verifyHeader($payload, $signature, $webhookSecret, self::SIGNATURE_TOLERANCE);
        } catch (SignatureVerificationException $e) {
            error_log('[Webhook] Signature verification failed: ' . $e->getMessage());
            return $this->json(['error' => 'Webhook verification failed'], 400);
        }

        // 2. Turn the verified payload into an application event.
        try {
            $event = $this->toEvent(Json::decodeArray($payload));
        } catch (JsonException | RuntimeException $e) {
            error_log('[Webhook] Invalid payload: ' . $e->getMessage());
            return $this->json(['error' => 'Invalid webhook payload'], 400);
        }

        // 3. Dispatch. A listener failure is a 500 so the gateway retries delivery.
        if ($event !== null) {
            try {
                $this->dispatcher->dispatch($event);
            } catch (Throwable $e) {
                error_log('[Webhook] Listener failed: ' . $e->getMessage());
                return $this->json(['error' => 'Webhook processing failed'], 500);
            }
        }

        return $this->json(['status' => 'success']);
    }

    /**
     * Map a Stripe event to an application event. Event types we don't care
     * about (customer.created, invoice.paid, ...) are acknowledged with null.
     *
     * @param  array<mixed>                                    $event
     * @throws RuntimeException                                If a required field is missing or mistyped
     * @return PaymentSucceededEvent|PaymentFailedEvent|null
     */
    private function toEvent(array $event): PaymentSucceededEvent | PaymentFailedEvent | null
    {
        return match (Arr::string($event, 'type', '')) {
            // charge.succeeded is the fallback for older events
            'payment_intent.succeeded', 'charge.succeeded'   => $this->succeeded(Arr::assoc($event, 'data.object')),
            'payment_intent.payment_failed', 'charge.failed' => $this->failed(Arr::assoc($event, 'data.object')),
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $object The Stripe PaymentIntent / Charge object
     */
    private function succeeded(array $object): PaymentSucceededEvent
    {
        return new PaymentSucceededEvent(
            transactionId: Arr::string($object, 'id'),
            amount: Arr::int($object, 'amount') / 100,
            currency: Arr::string($object, 'currency', 'USD'),
            metadata: Arr::assoc($object, 'metadata', []),
        );
    }

    /**
     * @param array<string, mixed> $object The Stripe PaymentIntent / Charge object
     */
    private function failed(array $object): PaymentFailedEvent
    {
        return new PaymentFailedEvent(
            message: Arr::string($object, 'last_payment_error.message', 'Payment failed'),
            context: $object,
        );
    }
}
