<?php
namespace App\Listeners;

use App\Events\UserRegistered;
use Rhapsody\Core\Event;
use Rhapsody\Core\Logger;
use Rhapsody\Core\Mailer;

/**
 * Handles sending a welcome email to a new user.
 *
 * Note: intentionally does not implement Rhapsody\Core\Events\ListenerInterface
 * or Rhapsody\Core\Contracts\ListenerInterface - both currently reference a
 * nonexistent 'Event' class in their own sub-namespace (a bug in the
 * arout/rhapsody-core package), which fatal-errors on class load if
 * implemented. EventDispatcher::dispatch() only requires a handle() method
 * to exist, so this works fine without formally implementing the interface.
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
     * @param UserRegistered $event
     */
    public function handle(Event $event): void
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
