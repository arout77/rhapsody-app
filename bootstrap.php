<?php
declare (strict_types = 1);

use Rhapsody\Core\Bootstrap\ServiceRegistrar;

/**
 * Application bootstrap. Runs once per request, after core's services are
 * registered and before modules boot.
 *
 * Add your own services here. You can also replace these core services with
 * your own subclasses: Mailer, PaymentGatewayInterface, DeferrerInterface,
 * ViewRenderer and Validator. Other core bindings are locked.
 */
return static function (ServiceRegistrar $services): void {
    // $services->singleton(App\Services\Billing::class);
    // $services->bind(Rhapsody\Core\Mailer::class, fn () => new App\Mail\CustomMailer());
};
