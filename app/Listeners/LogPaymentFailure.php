<?php
namespace App\Listeners;

use App\Events\PaymentFailedEvent;

class LogPaymentFailure
{
    public function handle(PaymentFailedEvent $event): void
    {
        // Write to a log file, database, or monitoring system
        $logMessage = sprintf(
            '[Payment Failed] %s | Context: %s',
            $event->message,
            json_encode($event->context)
        );

        // Use your preferred logger, e.g., Rhapsody's Logger
        error_log($logMessage);

        // Optionally, trigger a notification to the admin
        // $this->mailer->send('admin@example.com', 'Payment failure', $logMessage);
    }
}
