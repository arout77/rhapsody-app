<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Cache;
use Rhapsody\Core\Cache\CacheInterface;
use Rhapsody\Core\Response;
use Rhapsody\Core\Services\NotificationService;

/**
 * A minimal in-memory CacheInterface for tests — avoids touching the
 * filesystem-backed FileCacheDriver.
 */
class InMemoryCache implements CacheInterface
{
    private array $store = [];

    public function get(string $key, $default = null)
    {
        return $this->store[$key] ?? $default;
    }

    public function put(string $key, $value, int $minutes): void
    {
        $this->store[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->store);
    }

    public function forget(string $key): void
    {
        unset($this->store[$key]);
    }

    public function flush(): bool
    {
        $this->store = [];
        return true;
    }
}

/**
 * Overrides the two things that would otherwise hit the real world
 * (Composer\InstalledVersions and a live HTTP call to Packagist) so the
 * version-comparison and banner-injection logic can be tested in isolation.
 */
class TestableNotificationService extends NotificationService
{
    public ?string $fakeCurrentVersion         = '1.0.0';
    public ?string $fakeLatestFromPackagist    = null;
    public int $packagistFetchCallCount        = 0;

    protected function getCurrentVersion(): string
    {
        return $this->fakeCurrentVersion;
    }

    protected function fetchLatestVersionFromPackagist(): ?string
    {
        $this->packagistFetchCallCount++;
        return $this->fakeLatestFromPackagist;
    }
}

class NotificationServiceTest extends TestCase
{
    private TestableNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $cache         = new Cache(new InMemoryCache());
        $this->service = new TestableNotificationService($cache);
    }

    public function test_it_reports_an_update_when_a_newer_stable_version_exists(): void
    {
        $this->service->fakeCurrentVersion      = '1.0.0';
        $this->service->fakeLatestFromPackagist = '1.2.0';

        $update = $this->service->getAvailableUpdate();

        $this->assertNotNull($update);
        $this->assertSame('1.0.0', $update['current']);
        $this->assertSame('1.2.0', $update['latest']);
        $this->assertStringContainsString('packagist.org/packages/arout/rhapsody-core', $update['url']);
    }

    public function test_it_reports_no_update_when_already_current(): void
    {
        $this->service->fakeCurrentVersion      = '1.2.0';
        $this->service->fakeLatestFromPackagist = '1.2.0';

        $this->assertNull($this->service->getAvailableUpdate());
    }

    public function test_it_reports_no_update_when_ahead_of_packagist(): void
    {
        $this->service->fakeCurrentVersion      = '2.0.0';
        $this->service->fakeLatestFromPackagist = '1.2.0';

        $this->assertNull($this->service->getAvailableUpdate());
    }

    public function test_it_skips_comparison_entirely_for_dev_branch_installs(): void
    {
        $this->service->fakeCurrentVersion      = 'dev-main';
        $this->service->fakeLatestFromPackagist = '1.2.0';

        $this->assertNull($this->service->getAvailableUpdate());
    }

    public function test_it_caches_the_packagist_result_and_does_not_refetch(): void
    {
        $this->service->fakeCurrentVersion      = '1.0.0';
        $this->service->fakeLatestFromPackagist = '1.2.0';

        $this->service->getAvailableUpdate();
        $this->service->getAvailableUpdate();
        $this->service->getAvailableUpdate();

        $this->assertSame(1, $this->service->packagistFetchCallCount);
    }

    public function test_injectBanner_adds_banner_html_before_closing_body_when_update_available(): void
    {
        $this->service->fakeCurrentVersion      = '1.0.0';
        $this->service->fakeLatestFromPackagist = '1.2.0';

        $response = new Response();
        $response->setContent('<html><body><h1>Hello</h1></body></html>');

        $result = $this->service->injectBanner($response);

        $this->assertStringContainsString('rhapsody-update-banner', $result->getContent());
        $this->assertStringContainsString('1.2.0', $result->getContent());
        $this->assertStringContainsString('1.0.0', $result->getContent());
    }

    public function test_injectBanner_leaves_response_untouched_when_up_to_date(): void
    {
        $this->service->fakeCurrentVersion      = '1.2.0';
        $this->service->fakeLatestFromPackagist = '1.2.0';

        $original = '<html><body><h1>Hello</h1></body></html>';
        $response = new Response();
        $response->setContent($original);

        $result = $this->service->injectBanner($response);

        $this->assertSame($original, $result->getContent());
    }

    public function test_injectBanner_never_throws_even_if_the_update_check_blows_up(): void
    {
        $service = new class ($this->service) extends TestableNotificationService {
            public function __construct(TestableNotificationService $donor)
            {
                parent::__construct(new Cache(new InMemoryCache()));
            }
            protected function fetchLatestVersionFromPackagist(): ?string
            {
                throw new \RuntimeException('Packagist is on fire');
            }
        };

        $response = new Response();
        $response->setContent('<html><body>Hello</body></html>');

        $result = $service->injectBanner($response);

        $this->assertSame('<html><body>Hello</body></html>', $result->getContent());
    }
}
