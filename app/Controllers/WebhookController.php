<?php
namespace App\Controllers;

use App\Events\PaymentFailedEvent;
use App\Events\PaymentSucceededEvent;
use Rhapsody\Core\Controllers\BaseWebhookController;
use Rhapsody\Core\Events\EventDispatcher;
use Rhapsody\Core\Request;
use Rhapsody\Core\Response;
use Stripe\WebhookSignature;

class WebhookController extends BaseWebhookController
{
    public function __construct(
        protected EventDispatcher $dispatcher
    ) {}

    public function handle(Request $request): Response
    {
        $payload         = $request->getContent();
        $signatureHeader = $request->header('Stripe-Signature');
        $webhookSecret   = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? null;

        // If you use a different gateway, adapt this section accordingly.
        if (empty($webhookSecret)) {
            // Fallback: log and reject
            error_log('[Webhook] Missing STRIPE_WEBHOOK_SECRET in environment.');
            return $this->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            // Verify the signature (throws exception on failure)
            WebhookSignature::verifyHeader(
                $payload,
                $signatureHeader,
                $webhookSecret,
                $tolerance = 300// seconds
            );

            $data      = json_decode($payload, true);
            $eventType = $data['type'] ?? '';

            switch ($eventType) {
                case 'payment_intent.succeeded':
                    $this->dispatcher->dispatch(
                        new PaymentSucceededEvent(
                            transactionId: $data['data']['object']['id'],
                            amount: $data['data']['object']['amount'] / 100,
                            currency: $data['data']['object']['currency'] ?? 'USD',
                            metadata: $data['data']['object']['metadata'] ?? []
                        )
                    );
                    break;
                case 'charge.succeeded': // fallback for older events
                    $this->dispatcher->dispatch(
                        new PaymentSucceededEvent(
                            transactionId: $data['data']['object']['id'],
                            amount: $data['data']['object']['amount'] / 100,
                            currency: $data['data']['object']['currency'] ?? 'USD',
                            metadata: $data['data']['object']['metadata'] ?? []
                        )
                    );
                    break;

                case 'payment_intent.payment_failed':
                case 'charge.failed':
                    $error   = $data['data']['object']['last_payment_error'] ?? null;
                    $message = $error['message'] ?? 'Payment failed';
                    $this->dispatcher->dispatch(
                        new PaymentFailedEvent(
                            message: $message,
                            context: $data['data']['object']
                        )
                    );
                    break;

                default:
                    // Acknowledge other events (e.g., customer.created, invoice.paid)
                    // but do nothing.
                    break;
            }

            return $this->json(['status' => 'success']);

        } catch (\Exception $e) {
            // Log the full error for debugging (use a proper logger if available)
            error_log('[Webhook] Error: ' . $e->getMessage());
            return $this->json(['error' => 'Webhook verification failed'], 400);
        }
    }
}
