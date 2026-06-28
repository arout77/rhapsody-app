<?php
namespace App\Listeners;

use App\Events\PaymentSucceededEvent;
use Rhapsody\Core\Mailer;

class SendPaymentConfirmationEmail
{
    public function __construct(
        protected Mailer $mailer
    ) {}

    public function handle(PaymentSucceededEvent $event): void
    {
        // Fetch the order from the database using the transaction ID or metadata
        // $order = $this->orderRepository->findByTransactionId($event->transactionId);

                                           // For demonstration, we'll just send a generic confirmation.
        $to      = 'customer@example.com'; // Replace with actual customer email
        $subject = 'Payment Confirmation';
        $body    = sprintf(
            "Thank you! Your payment of %.2f %s was successful.\nTransaction ID: %s",
            $event->amount,
            $event->currency,
            $event->transactionId
        );

        $this->mailer->send($to, $subject, $body);
    }
}
