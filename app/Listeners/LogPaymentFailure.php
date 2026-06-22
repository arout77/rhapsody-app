<?php
namespace App\Listeners;

use Rhapsody\Core\Events\Event;
use Rhapsody\Core\Events\ListenerInterface;
use Rhapsody\Core\Events\PaymentFailedEvent;

class LogPaymentFailure implements ListenerInterface
{
    public function handle(Event $event): void
    {
        if (! $event instanceof PaymentFailedEvent) {
            return;
        }
        // Log the failure (e.g., with Monolog)
        // logger()->error('Payment failed', ['message' => $event->message, 'context' => $event->context]);
    }
}
