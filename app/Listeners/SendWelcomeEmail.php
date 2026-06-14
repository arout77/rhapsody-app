<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Core\Events\Event;
use Core\Events\ListenerInterface;
use Core\Logger;
use Core\Mailer;

/**
 * Handles sending a welcome email to a new user.
 */
class SendWelcomeEmail implements ListenerInterface
{
    private Logger $logger;

    /**
     * @param Mailer $mailer The mailer service, injected by the container.
     */
    public function __construct( protected Mailer $mailer )
    {
        // Example of another dependency for logging
        $logPath      = dirname( __DIR__, 2 ) . '/storage/logs/app.log';
        $this->logger = new Logger( $logPath );
    }

    /**
     * Handle the UserRegistered event.
     *
     * @param UserRegistered $event
     */
    public function handle( Event $event ): void
    {
        if ( !$event instanceof UserRegistered ) {
            return;
        }

        $user     = $event->user;
        $to       = $user->getEmail();
        $subject  = 'Welcome to Rhapsody!';
        $htmlBody = "<p>Hi {$user->getName()},</p><p>Thank you for registering. We're excited to have you!</p>";

        try {
            $this->mailer->send( $to, $subject, $htmlBody );
            $this->logger->log( "Welcome email sent to {$to}", 'INFO' );
        } catch ( \Exception $e ) {
            $this->logger->log( "Failed to send welcome email to {$to}: " . $e->getMessage(), 'ERROR' );
        }
    }
}
