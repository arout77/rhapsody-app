<?php
namespace App\Providers;

use App\Events\AiModelUnavailableEvent;
use App\Events\PaymentFailedEvent;
use App\Events\PaymentSucceededEvent;
use App\Events\UserRegistered;
use App\Listeners\LogPaymentFailure;
use App\Listeners\NotifyAdminOfModelIssue;
use App\Listeners\SendPaymentConfirmationEmail;
use App\Listeners\SendWelcomeEmail;
use App\Listeners\UpdateOrderStatus;
use Rhapsody\Core\Events\ModuleBootFailed;
use Rhapsody\Core\Events\ModuleHealthCheckFailed;
use Rhapsody\Core\Listeners\NotifyDevsOfModuleFailure;
use Rhapsody\Core\Listeners\NotifyDevsOfModuleHealthFailure;

class EventServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected array $listen = [
        UserRegistered::class          => [
            SendWelcomeEmail::class,
        ],
        PaymentSucceededEvent::class   => [
            SendPaymentConfirmationEmail::class,
            UpdateOrderStatus::class,
        ],
        PaymentFailedEvent::class      => [
            LogPaymentFailure::class,
        ],
        AiModelUnavailableEvent::class => [
            NotifyAdminOfModelIssue::class,
        ],
        ModuleBootFailed::class        => [
            // Emails MODULE_ALERT_EMAIL and/or posts to MODULE_ALERT_WEBHOOK_URL
            // (set at least one in .env) the first time an installed module
            // fails to boot, then stays quiet on that module for 24h so a
            // module broken on every request doesn't spam. Swap this for your
            // own App\Listeners class if you need different alerting logic.
            NotifyDevsOfModuleFailure::class,
        ],
        ModuleHealthCheckFailed::class => [
            // Same alerting channels/dedupe as above, but for a module that
            // booted fine and later failed its own healthCheck() — only
            // fires when `module:health` actually runs, so this needs a
            // cron entry (or similar) calling it on a schedule to be useful.
            NotifyDevsOfModuleHealthFailure::class,
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
