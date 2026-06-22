<?php
namespace App\Listeners;

use Rhapsody\Core\Events\Event;
use Rhapsody\Core\Events\ListenerInterface;
use Rhapsody\Core\Events\PaymentSucceededEvent;

class UpdateOrderStatus implements ListenerInterface
{
    public function handle(Event $event): void
    {
        if (! $event instanceof PaymentSucceededEvent) {
            return;
        }
        // Update your order status in the database
    }
}
