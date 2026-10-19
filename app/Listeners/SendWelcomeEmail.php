<?php
namespace App\Listeners;

use App\Events\UserRegistered;
use Rhapsody\Core\Logger;
use Rhapsody\Core\Mailer;

/**
 * Handles sending a welcome email to a new user.
 *
 * EventDispatcher::dispatch() only requires a public handle() method, so this
 * listener takes a plain `object` and narrows it with instanceof. That keeps it
 * independent of where the framework keeps its base Event class
 * (Rhapsody\Core\Event was moved to Rhapsody\Core\Events\Event).
 */
class SendWelcomeEmail
{
    private Logger $logger;

    /**
     * @param Mailer $mailer The mailer service, injected by the container.
     */
    public function __construct(protected Mailer $mailer)
    {
        // Example of another dependency for logging
        $logPath      = dirname(__DIR__, 2) . '/storage/logs/app.log';
        $this->logger = new Logger($logPath);
    }

    /**
     * Handle the UserRegistered event.
     *
     * @param object $event Ignored unless it is a UserRegistered event.
     */
    public function handle(object $event): void
    {
        if (! $event instanceof UserRegistered) {
            return;
        }

        $user     = $event->user;
        $to       = $user->getEmail();
        $subject  = 'Welcome to Rhapsody!';
        $htmlBody = "<p>Hi {$user->getName()},</p><p>Thank you for registering. We're excited to have you!</p>";

        try {
            $this->mailer->send($to, $subject, $htmlBody);
            $this->logger->log("Welcome email sent to {$to}", 'INFO');
        } catch (\Exception $e) {
            $this->logger->log("Failed to send welcome email to {$to}: " . $e->getMessage(), 'ERROR');
        }
    }
}
