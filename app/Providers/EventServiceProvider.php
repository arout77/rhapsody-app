<?php
namespace App\Providers;

use App\Events\PaymentFailedEvent;
use App\Events\PaymentSucceededEvent;
use App\Events\UserRegistered;
use App\Listeners\LogPaymentFailure;
use App\Listeners\SendPaymentConfirmationEmail;
use App\Listeners\SendWelcomeEmail;
use App\Listeners\UpdateOrderStatus;

class EventServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected array $listen = [
        UserRegistered::class        => [
            SendWelcomeEmail::class,
        ],
        PaymentSucceededEvent::class => [
            SendPaymentConfirmationEmail::class,
            UpdateOrderStatus::class,
        ],
        PaymentFailedEvent::class    => [
            LogPaymentFailure::class,
        ],
    ];

    /**
     * @return mixed
     */
    public function getListeners(): array
    {
        return $this->listen;
    }
}
