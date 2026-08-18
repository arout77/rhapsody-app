<?php
namespace App\Listeners;

use App\Events\AiModelUnavailableEvent;
use Rhapsody\Core\Cache;
use Rhapsody\Core\Mailer;

/**
 * Emails the configured admin when a Gemini model becomes invalid,
 * deprecated, or unavailable — this is a config problem (a stale
 * GEMINI_MODEL value), not a transient failure, so it needs a human to
 * update .env, not just a log line.
 *
 * Deduped per model for an hour so a burst of concurrent user requests
 * hitting the same broken model doesn't send one email per request.
 */
class NotifyAdminOfModelIssue
{
    private const DEDUP_MINUTES = 60;

    public function __construct(
        private Mailer $mailer,
        private Cache $cache
    ) {
    }

    public function handle(AiModelUnavailableEvent $event): void
    {
        $to = $_ENV['MAIL_ADMIN_EMAIL'] ?? null;
        if (! $to) {
            error_log('NotifyAdminOfModelIssue: MAIL_ADMIN_EMAIL is not set — cannot send alert.');
            return;
        }

        $dedupKey = 'ai_model_alert_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $event->model ?? 'unknown');
        if ($this->cache->has($dedupKey)) {
            return;
        }

        $model   = htmlspecialchars($event->model ?? 'unknown', ENT_QUOTES);
        $message = htmlspecialchars($event->message, ENT_QUOTES);

        $subject = "Action needed: Gemini model \"{$event->model}\" is unavailable";
        $body    = "<p>Requests to the Gemini API are failing because the configured model "
            . "(<code>{$model}</code>) is invalid, deprecated, or no longer available.</p>"
            . "<p><strong>Provider message:</strong> {$message}</p>"
            . "<p>Update <code>GEMINI_MODEL</code> in your <code>.env</code> file to a currently "
            . "available model, then restart your web server.</p>"
            . "<p>This alert won't be sent again for this model for " . self::DEDUP_MINUTES . " minutes.</p>";

        try {
            $this->mailer->send($to, $subject, $body);
            $this->cache->put($dedupKey, true, self::DEDUP_MINUTES);
        } catch (\Throwable $e) {
            // Don't let a broken mailer take down the request that's
            // already failing for an unrelated reason — just log it.
            error_log('NotifyAdminOfModelIssue: failed to send alert email: ' . $e->getMessage());
        }
    }
}
