<?php
declare(strict_types=1);

namespace Tests\Unit\Snapshot;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Exceptions\HttpException;
use Rhapsody\Core\Snapshot\Redactor;
use Rhapsody\Core\Snapshot\SnapshotBuilder;
use Rhapsody\Core\Snapshot\SnapshotSettings;

class SnapshotBuilderTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function debugData(): array
    {
        return [
            'php_version'    => '8.4.1',
            'app_version'    => '1.1.0',
            'app_env'        => 'development',
            'execution_time' => 42.5,
            'memory_usage'   => 3.2,
            'response_code'  => 200,
            'session'        => ['user_id' => 42, '_csrf' => 'abc123'],
            'request'        => [
                'method'     => 'POST',
                'uri'        => '/login?token=abc',
                'full_url'   => 'http://localhost/login?token=abc&page=2',
                'ip'         => '127.0.0.1',
                'headers'    => ['Authorization' => 'Bearer abc.def', 'Cookie' => 'PHPSESSID=zzz', 'User-Agent' => 'test'],
                'query'      => ['token' => 'abc', 'page' => '2'],
                'body'       => '{"email":"a@b.c","password":"hunter2","profile":{"api_key":"k-1"}}',
                'middleware' => ['auth'],
            ],
            'route'          => ['method' => 'POST', 'path' => '/login', 'controller' => 'AuthController', 'action' => 'login'],
            'env'            => ['APP_ENV' => 'development', 'DB_PASS' => 'rootpw', 'DB_HOST' => 'localhost', 'SOMETHING_ELSE' => 'x'],
            'queries'        => [
                ['sql' => 'UPDATE users SET password = ? WHERE id = ?', 'params' => ['$2y$10$hash', 7], 'executionMS' => 0.004, 'is_n_plus_1' => false, 'caller' => ['file' => 'app/Models/User.php', 'line' => 12]],
                ['sql' => 'SELECT * FROM posts WHERE id = ?', 'params' => [7], 'executionMS' => 0.001, 'is_n_plus_1' => true],
            ],
            'query_groups'   => [['fingerprint' => 'x', 'count' => 4, 'repeated' => true, 'total_ms' => 4.0, 'example_sql' => 'SELECT * FROM posts WHERE id = ?', 'callers' => ['app/Models/Post.php:9']]],
            'cache_stats'    => ['hits' => 1, 'misses' => 2],
            'middleware_trace' => [['class' => 'AuthMiddleware', 'type' => 'route', 'route' => '/login', 'duration' => 1.5]],
            'container_trace'  => [
                ['class' => 'A', 'duration' => 1.0, 'called_by' => 'X'],
                ['class' => 'B', 'duration' => 9.0, 'called_by' => 'Y', 'proxy' => true],
            ],
            'memory'         => ['used_mb' => 3.2, 'status' => 'ok'],
            'logs'           => ['php' => "line1\nline2 password=secret\nline3", 'apache' => 'Log file not found or not readable at: C:\\x'],
        ];
    }

    private function builder(?SnapshotSettings $settings = null): SnapshotBuilder
    {
        return new SnapshotBuilder(new Redactor(), $settings ?? new SnapshotSettings());
    }

    public function test_the_snapshot_contains_no_raw_secret_anywhere(): void
    {
        $json = $this->builder()->build($this->debugData(), null, new DateTimeImmutable('2026-11-04 18:00:00'))->toJson();

        foreach (['hunter2', 'abc.def', 'PHPSESSID=zzz', 'rootpw', 'k-1', 'abc123', '$2y$10$hash', 'secret'] as $secret) {
            $this->assertStringNotContainsString($secret, $json, "{$secret} leaked into the snapshot");
        }
        $this->assertStringContainsString('"page": "2"', $json);
        $this->assertStringContainsString('"User-Agent": "test"', $json);
    }

    public function test_sections_are_present_and_shaped_for_later_consumers(): void
    {
        $snapshot = $this->builder()->build($this->debugData(), null, new DateTimeImmutable('2026-11-04 18:00:00.123456'));
        $data     = $snapshot->toArray();

        $this->assertSame(1, $data['schema']);
        $this->assertStringStartsWith('20261104-180000-123456-', $snapshot->id);
        $this->assertSame(200, $data['response']['status']);
        $this->assertSame(42.5, $data['performance']['time_ms']);
        $this->assertSame('POST', $data['request']['method']);
        $this->assertSame('[REDACTED]', $data['request']['headers']['Authorization']);
        $this->assertSame(['user_id' => '[REDACTED int]', '_csrf' => '[REDACTED string]'], $data['session']);
        $this->assertSame('AuthController', $data['route']['controller']);
        $this->assertNull($data['exception']);
    }

    public function test_only_allowlisted_environment_variables_are_included(): void
    {
        $env = $this->builder(new SnapshotSettings(envAllowlist: ['SOMETHING_ELSE']))->build($this->debugData())->toArray()['environment'];

        $this->assertSame('development', $env['APP_ENV']);
        $this->assertSame('localhost', $env['DB_HOST']);
        $this->assertSame('x', $env['SOMETHING_ELSE']);
        $this->assertArrayNotHasKey('DB_PASS', $env);

        $default = $this->builder()->build($this->debugData())->toArray()['environment'];
        $this->assertArrayNotHasKey('SOMETHING_ELSE', $default);
    }

    public function test_query_params_follow_the_sql_and_repeated_shapes_are_summarised(): void
    {
        $queries = $this->builder()->build($this->debugData())->toArray()['queries'];

        $this->assertSame(2, $queries['total']);
        $this->assertSame('[REDACTED]', $queries['items'][0]['params']);
        $this->assertSame([7], $queries['items'][1]['params']);
        $this->assertSame(4.0, $queries['items'][0]['time_ms']);
        $this->assertSame('app/Models/User.php:12', $queries['items'][0]['caller']);
        $this->assertTrue($queries['items'][1]['repeated']);
        $this->assertSame(4, $queries['n_plus_one'][0]['count']);
    }

    public function test_the_query_list_is_capped(): void
    {
        $data            = $this->debugData();
        $data['queries'] = array_fill(0, 10, ['sql' => 'SELECT 1', 'executionMS' => 0.0]);

        $queries = $this->builder(new SnapshotSettings(maxQueries: 3))->build($data)->toArray()['queries'];

        $this->assertSame(10, $queries['total']);
        $this->assertSame(3, $queries['recorded']);
        $this->assertCount(3, $queries['items']);
    }

    public function test_the_body_is_redacted_by_key_and_truncated(): void
    {
        $body = $this->builder()->build($this->debugData())->toArray()['request']['body'];

        $this->assertStringContainsString('"email":"a@b.c"', $body['content']);
        $this->assertStringContainsString('"password":"[REDACTED]"', $body['content']);
        $this->assertFalse($body['truncated']);

        $data                    = $this->debugData();
        $data['request']['body'] = 'name=' . str_repeat('x', 100) . '&password=hunter2';
        $short                   = $this->builder(new SnapshotSettings(maxBodyBytes: 20))->build($data)->toArray()['request']['body'];

        $this->assertTrue($short['truncated']);
        $this->assertSame(20, strlen($short['content']));
        $this->assertGreaterThan(100, $short['bytes']);
    }

    public function test_an_empty_body_is_null(): void
    {
        $data                    = $this->debugData();
        $data['request']['body'] = null;

        $this->assertNull($this->builder()->build($data)->toArray()['request']['body']);
    }

    public function test_logs_are_unescaped_trimmed_scrubbed_and_missing_logs_are_null(): void
    {
        $data         = $this->debugData();
        $data['logs'] = ['php' => 'a &lt;b&gt; &amp; c' . "\n" . 'two' . "\n" . 'three password=x', 'apache' => 'Error reading log file: nope'];

        $logs = $this->builder(new SnapshotSettings(logLines: 2))->build($data)->toArray()['logs'];

        $this->assertSame("two\nthree password=[REDACTED]", $logs['php']);
        $this->assertNull($logs['apache']);

        $plain = $this->builder()->build(['logs' => ['php' => 'x &lt; y']])->toArray()['logs'];
        $this->assertSame('x < y', $plain['php']);
    }

    public function test_slowest_services_come_first(): void
    {
        $services = $this->builder()->build($this->debugData())->toArray()['services'];

        $this->assertSame('B', $services[0]['class']);
        $this->assertTrue($services[0]['lazy']);
        $this->assertSame('A', $services[1]['class']);
    }

    public function test_an_exception_is_described_with_a_scrubbed_message_and_a_short_trace(): void
    {
        $exception = new HttpException(500, 'DB failed: mysql://root:s3cret@db/app', 0, new \RuntimeException('inner password=hunter2'));

        $described = $this->builder()->build($this->debugData(), $exception)->toArray()['exception'];

        $this->assertSame(HttpException::class, $described['class']);
        $this->assertSame(500, $described['http_status']);
        $this->assertStringNotContainsString('s3cret', $described['message']);
        $this->assertSame(\RuntimeException::class, $described['previous'][0]['class']);
        $this->assertStringNotContainsString('hunter2', $described['previous'][0]['message']);
        $this->assertLessThanOrEqual(15, count($described['trace']));
    }

    public function test_missing_sections_do_not_break_the_build(): void
    {
        $data = $this->builder()->build([])->toArray();

        $this->assertSame(0, $data['response']['status']);
        $this->assertSame(0, $data['queries']['total']);
        $this->assertSame([], $data['session']);
        $this->assertNull($data['route']);
        $this->assertNull($data['logs']['php']);
    }
}
