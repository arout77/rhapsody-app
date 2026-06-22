<?php
namespace App\Controllers;

use Rhapsody\Core\Controllers\BaseWebhookController;
use Rhapsody\Core\Events\EventDispatcher;
use Rhapsody\Core\Events\PaymentFailedEvent;
use Rhapsody\Core\Events\PaymentSucceededEvent;
use Rhapsody\Core\Request;
use Rhapsody\Core\Response;

class WebhookController extends BaseWebhookController
{
    public function __construct(
        protected EventDispatcher $dispatcher
    ) {}

    public function handle(Request $request): Response
    {
        // Verify the webhook signature (example for Stripe)
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret    = $_ENV['STRIPE_WEBHOOK_SECRET'];

        try {
            // Use Omnipay or a dedicated library to verify signature
            // For Stripe, you can use \Stripe\Webhook::constructEvent()
            // For simplicity, we'll assume verification is done.

            $data      = json_decode($payload, true);
            $eventType = $data['type'] ?? '';

            switch ($eventType) {
                case 'payment_intent.succeeded':
                    $this->dispatcher->dispatch(
                        new PaymentSucceededEvent(
                            transactionId: $data['data']['object']['id'],
                            amount: $data['data']['object']['amount'] / 100,
                            metadata: $data['data']['object']['metadata'] ?? []
                        )
                    );
                    break;
                case 'payment_intent.payment_failed':
                    $this->dispatcher->dispatch(
                        new PaymentFailedEvent(
                            message: $data['data']['object']['last_payment_error']['message'] ?? 'Payment failed',
                            context: $data['data']['object']
                        )
                    );
                    break;
                default:
                    // Ignore other events
                    break;
            }

            return $this->json(['status' => 'success']);
        } catch (\Exception $e) {
            // Log the error and return a 400
            return $this->json(['error' => 'Webhook error'], 400);
        }
    }
}
