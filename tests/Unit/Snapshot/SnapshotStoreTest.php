<?php
declare(strict_types=1);

namespace Tests\Unit\Snapshot;

use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Snapshot\RequestSnapshot;
use Rhapsody\Core\Snapshot\SnapshotRecorder;
use Rhapsody\Core\Snapshot\SnapshotSettings;
use Rhapsody\Core\Snapshot\SnapshotStore;

class SnapshotStoreTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rhapsody-snapshots-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . DIRECTORY_SEPARATOR . '{,.}*', GLOB_BRACE) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
    }

    private function snapshot(string $id): RequestSnapshot
    {
        return new RequestSnapshot($id, '2026-11-04T18:00:00.000-05:00', ['request' => ['method' => 'GET']]);
    }

    public function test_it_writes_json_and_creates_a_guarded_directory(): void
    {
        $store = new SnapshotStore($this->dir, 5);
        $path  = $store->save($this->snapshot('20261104-180000-000001-aaaa'));

        $this->assertFileExists($path);
        $this->assertFileExists($this->dir . DIRECTORY_SEPARATOR . '.htaccess');
        $this->assertFileExists($this->dir . DIRECTORY_SEPARATOR . 'index.html');
        $this->assertStringContainsString('Require all denied', (string) file_get_contents($this->dir . DIRECTORY_SEPARATOR . '.htaccess'));

        $read = $store->read($path);
        $this->assertSame(1, $read['schema']);
        $this->assertSame('20261104-180000-000001-aaaa', $read['id']);
        $this->assertSame('GET', $read['request']['method']);
        $this->assertSame([], glob($this->dir . DIRECTORY_SEPARATOR . '*.tmp'));
    }

    public function test_only_the_newest_snapshots_are_kept(): void
    {
        $store = new SnapshotStore($this->dir, 3);
        foreach (range(1, 5) as $n) {
            $store->save($this->snapshot(sprintf('20261104-180000-%06d-aaaa', $n)));
        }

        $names = array_map('basename', $store->paths());

        $this->assertSame([
            '20261104-180000-000005-aaaa.json',
            '20261104-180000-000004-aaaa.json',
            '20261104-180000-000003-aaaa.json',
        ], $names);
        $this->assertSame($this->dir . DIRECTORY_SEPARATOR . '20261104-180000-000005-aaaa.json', $store->latestPath());
    }

    public function test_an_empty_or_missing_directory_has_no_snapshots(): void
    {
        $store = new SnapshotStore($this->dir . DIRECTORY_SEPARATOR . 'nope');

        $this->assertSame([], $store->paths());
        $this->assertNull($store->latestPath());
    }

    public function test_settings_default_safely_and_read_the_config_section(): void
    {
        $defaults = SnapshotSettings::fromConfig([]);
        $this->assertTrue($defaults->enabled);
        $this->assertSame(25, $defaults->keep);
        $this->assertSame(['/img/'], $defaults->ignorePrefixes);

        $custom = SnapshotSettings::fromConfig(['debug' => ['snapshots' => [
            'enabled' => false, 'keep' => 7, 'max_body_bytes' => 0, 'redact_keys' => ['ssn', 5, ''], 'ignore_prefixes' => [],
        ]]]);
        $this->assertFalse($custom->enabled);
        $this->assertSame(7, $custom->keep);
        $this->assertSame(8192, $custom->maxBodyBytes);
        $this->assertSame(['ssn'], $custom->redactKeys);
        $this->assertSame([], $custom->ignorePrefixes);
    }

    public function test_snapshots_are_development_only(): void
    {
        $this->assertTrue(SnapshotSettings::isDevelopment(['app_env' => 'development']));
        $this->assertFalse(SnapshotSettings::isDevelopment(['app_env' => 'production']));
        $this->assertFalse(SnapshotSettings::isDevelopment([]));

        $this->assertNull(SnapshotRecorder::recordCollected(['app_env' => 'production']));
        $this->assertNull(SnapshotRecorder::recordCollected([]));
        $this->assertNull(SnapshotRecorder::recordCollected(['app_env' => 'development', 'debug' => ['snapshots' => ['enabled' => false]]]));
    }
}
