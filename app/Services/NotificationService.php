<?php
/**
 * This service checks Git for updates to the framework.
 * It uses a flag in the cache to determine when the last
 * check was made; and runs once every 6 hours to prevent
 * hitting GitHub API limits.
 */
namespace App\Services;

use Core\Cache;
use Core\Response;

class NotificationService
{
    // Define your current running version string and the target repository endpoint
    protected const CURRENT_VERSION = 'v1.4.8';
    protected const GITHUB_API_URL  = 'https://api.github.com/repos/arout77/rhapsody/releases/latest';

    /**
     * @param Cache $cache
     */
    public function __construct(protected Cache $cache)
    {}

    /**
     * Injects an update notification banner into an HTML response if an update is available.
     */
    public function injectBanner(Response $response): Response
    {
        // 1. Run the silent automated background update check (Throttled to every 12 hours)
        $this->checkForUpdatesThrottled();

        // 2. Look for the cached banner trigger key
        $newVersion = $this->cache->get('update_available');

        if (! $newVersion) {
            return $response; // No update or up-to-date, do nothing
        }

        $content = $response->getContent(); //

        if (! str_contains($content, '</body>')) { //
            return $response;
        }

        $bannerHtml = $this->createBannerHtml($newVersion); //

        $modifiedContent = str_ireplace('</body>', $bannerHtml . '</body>', $content); //
        $response->setContent($modifiedContent);                                       //

        return $response;
    }

    /**
     * Safely queries the GitHub API while enforcing a strict cache-based cooldown window.
     */
    protected function checkForUpdatesThrottled(): void
    {
        // If our 6-hour cooling period is still active, exit immediately (0ms overhead)
        if ($this->cache->get('rhapsody_last_update_check')) {
            return;
        }

        // Set the 6-hour lock key instantly to prevent concurrent page loads from double-pinging
        $this->cache->set('rhapsody_last_update_check', true, 26600);

        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Rhapsody-Framework-Update-Checker\r\n",
            ],
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents(self::GITHUB_API_URL, false, $context);

        if ($response === false) {
            return; // Silently abort if the GitHub API is down, rate-limited, or offline
        }

        $data          = json_decode($response, true);
        $latestVersion = $data['tag_name'] ?? null;

        // Compare GitHub's newest tag release with your active local instance version
        if ($latestVersion && version_compare($latestVersion, self::CURRENT_VERSION, '>')) {
            $this->cache->set('update_available', $latestVersion, 86400 * 4); // Keep banner cache for 96 hours
        } else {
            $this->cache->delete('update_available'); // User is up-to-date, clear old values
        }
    }

    /**
     * @param string $version
     */
    private function createBannerHtml(string $version): string
    {
        // Using inline styles to ensure the banner is always visible and styled correctly
        return <<<HTML
            <div id="rhapsody-update-banner" style="position: fixed; bottom: 0; left: 0; width: 100%; background-color: #1F2937; color: #F9FAFB; padding: 12px; z-index: 99999; display: flex; justify-content: center; align-items: center; font-family: sans-serif; font-size: 14px; border-top: 1px solid #4B5563;">
                <span class="bg-pink-700 text-white animate-pulse" style="font-weight: bold; font-size: 10px; padding: 2px 8px; border-radius: 9999px; margin-right: 12px;">UPDATE</span>
                A new version of Rhapsody is available! &nbsp;<strong>($version)</strong>
                <a href="#" id="update-notification-link" data-version="$version" style="color: #60A5FA; font-weight: bold; text-decoration: underline; margin-left: 16px;">View Details & Release Notes</a>
            </div>
        HTML;
    }
}
