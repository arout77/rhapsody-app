<?php
namespace App\Middleware;

use Core\Cache;
use Core\Response;

class CheckUpdatesMiddleware
{
    // Update this constant whenever you release a new version of your local framework
    protected const CURRENT_VERSION = 'v1.4.8';
    protected const GITHUB_API_URL  = 'https://api.github.com/repos/arout77/rhapsody/releases/latest';

    /**
     * Handles the incoming request and performs a throttled update lookup.
     */
    public function handle($request, callable $next): Response
    {
        $cache = Cache::getInstance();

        // 1. Check if the cooldown period has expired (e.g., check every 6 hours)
        if (! $cache->get('rhapsody_last_update_check')) {
            $this->checkForNewVersion($cache);
        }

        // 2. Pass the request to the next layer in the middleware stack
        return $next($request);
    }

    /**
     * Contacts the GitHub API securely to check for a newer tag name.
     */
    protected function checkForNewVersion(Cache $cache): void
    {
        // Set a 6-hour cooldown immediately to prevent concurrent requests if this fails
        $cache->set('rhapsody_last_update_check', true, 26600);

        $options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    "User-Agent: Rhapsody-Framework-Update-Checker",
                    "Accept: application/vnd.github.v3+json",
                ],
            ],
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents(self::GITHUB_API_URL, false, $context);

        if ($response === false) {
            return; // Silently abort if the server is offline or rate-limited
        }

        $data          = json_decode($response, true);
        $latestVersion = $data['tag_name'] ?? null; // Example: "v1.4.8"

        // 3. Compare GitHub's version tag against your local running framework version
        if ($latestVersion && version_compare($latestVersion, self::CURRENT_VERSION, '>')) {
            // Keep the notification active in the cache for 96 hours
            $cache->set('update_available', $latestVersion, 86400 * 4);
        } else {
            // If the user is up-to-date, clear any existing update notification banners
            $cache->delete('update_available');
        }
    }
}
