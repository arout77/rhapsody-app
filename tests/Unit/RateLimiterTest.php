<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Cache;
use Rhapsody\Core\Services\RateLimiter;

// Reuses the InMemoryCache fake already declared in NotificationServiceTest.php
// (same namespace, identical CacheInterface implementation).

class RateLimiterTest extends TestCase
{
    private RateLimiter $limiter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->limiter = new RateLimiter(new Cache(new InMemoryCache()), []);
    }

    public function test_requests_under_the_limit_are_allowed(): void
    {
        $result = $this->limiter->attempt('1.2.3.4', 5, 60, 300);

        $this->assertTrue($result['allowed']);
        $this->assertSame(0, $result['retry_after']);
        $this->assertSame(4, $result['remaining']);
    }

    public function test_exceeding_the_limit_blocks_the_request(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->limiter->attempt('1.2.3.4', 3, 60, 300);
        }

        $result = $this->limiter->attempt('1.2.3.4', 3, 60, 300);

        $this->assertFalse($result['allowed']);
        $this->assertSame(300, $result['retry_after']);
        $this->assertSame(0, $result['remaining']);
    }

    public function test_the_block_persists_on_subsequent_attempts(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->limiter->attempt('1.2.3.4', 3, 60, 300);
        }

        $result = $this->limiter->attempt('1.2.3.4', 3, 60, 300);

        $this->assertFalse($result['allowed']);
    }

    public function test_different_keys_are_tracked_independently(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->limiter->attempt('1.2.3.4', 3, 60, 300);
        }

        $result = $this->limiter->attempt('5.6.7.8', 3, 60, 300);

        $this->assertTrue($result['allowed'], 'A different key should not be affected by another key being blocked.');
    }

    /**
     * Regression test for a real bug: once a block's expiry timestamp
     * passed, only the expiry marker was cleared — the request counter
     * (which uses its own, separate TTL based on $window) was left with its
     * stale over-limit value. Whenever block_duration <= window (a
     * perfectly valid configuration), this caused the block to immediately
     * re-trigger the instant it expired, extending it indefinitely.
     */
    public function test_a_new_attempt_is_allowed_once_the_block_has_expired(): void
    {
        $cache = new InMemoryCache();
        $limiter = new RateLimiter(new Cache($cache), []);

        for ($i = 0; $i < 4; $i++) {
            $limiter->attempt('1.2.3.4', 3, 60, 300);
        }
        $this->assertFalse($limiter->attempt('1.2.3.4', 3, 60, 300)['allowed']);

        // Simulate the block having already expired (avoids a real sleep()
        // in the test) by directly backdating the stored expiry timestamp.
        $cache->put('rate_limit:1.2.3.4:block_expiry', time() - 1, 1440);

        $result = $limiter->attempt('1.2.3.4', 3, 60, 300);

        $this->assertTrue($result['allowed'], 'A request after the block expires should be allowed, not immediately re-blocked by the stale counter.');
    }

    public function test_the_counter_resets_cleanly_after_a_block_expires(): void
    {
        $cache   = new InMemoryCache();
        $limiter = new RateLimiter(new Cache($cache), []);

        for ($i = 0; $i < 4; $i++) {
            $limiter->attempt('1.2.3.4', 3, 60, 300);
        }
        $cache->put('rate_limit:1.2.3.4:block_expiry', time() - 1, 1440);

        // First attempt after expiry should not just be "allowed" but should
        // reflect a genuinely fresh count (remaining = max - 1), not a
        // counter that silently carried over stale usage.
        $result = $limiter->attempt('1.2.3.4', 3, 60, 300);

        $this->assertSame(2, $result['remaining']);
    }
}
