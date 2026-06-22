<?php
namespace App\Listeners;

use Rhapsody\Core\Events\Event;
use Rhapsody\Core\Events\ListenerInterface;
use Rhapsody\Core\Events\PaymentSucceededEvent;

class SendPaymentConfirmationEmail implements ListenerInterface
{
    public function handle(Event $event): void
    {
        if (! $event instanceof PaymentSucceededEvent) {
            return;
        }
        // Send email to customer using your mailer
        // e.g., Mail::to($user->email)->send(new PaymentConfirmation($event));
    }
}
