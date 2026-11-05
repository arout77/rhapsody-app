<?php
declare(strict_types=1);

namespace Tests\Unit\Snapshot;

use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Snapshot\QueryFingerprint;

class QueryFingerprintTest extends TestCase
{
    public function test_literals_and_whitespace_collapse_to_one_shape(): void
    {
        $a = QueryFingerprint::normalize("SELECT * FROM posts\n WHERE id = 7 AND slug = 'hello'");
        $b = QueryFingerprint::normalize("SELECT *   FROM posts WHERE id = 912 AND slug = 'it''s a \\' test'");

        $this->assertSame($a, $b);
        $this->assertSame('SELECT * FROM posts WHERE id = ? AND slug = ?', $a);
    }

    public function test_digits_inside_identifiers_and_strings_are_not_mistaken_for_numbers(): void
    {
        $this->assertSame('SELECT t1.col2 FROM t1 WHERE t1.name = ?', QueryFingerprint::normalize("SELECT t1.col2 FROM t1 WHERE t1.name = 'a 5 b'"));
    }

    public function test_in_lists_of_any_length_share_a_shape(): void
    {
        $this->assertSame(
            QueryFingerprint::normalize('SELECT * FROM posts WHERE id IN (1, 2, 3)'),
            QueryFingerprint::normalize('SELECT * FROM posts WHERE id IN (4,5,6,7,8,9)')
        );
    }

    public function test_a_shape_repeated_more_than_three_times_is_flagged(): void
    {
        $queries = [];
        for ($i = 1; $i <= 4; $i++) {
            $queries[] = ['sql' => "SELECT * FROM comments WHERE post_id = {$i}", 'executionMS' => 0.002, 'caller' => ['file' => 'app/Controllers/PostController.php', 'line' => 40]];
        }
        $queries[] = ['sql' => 'SELECT * FROM users WHERE id = 1', 'executionMS' => 0.001];

        $report = QueryFingerprint::analyze($queries);

        $this->assertCount(5, $report->queries);
        $this->assertCount(4, $report->alerts);
        $this->assertTrue($report->queries[0]['is_n_plus_1']);
        $this->assertFalse($report->queries[4]['is_n_plus_1']);

        $this->assertCount(2, $report->groups);
        $this->assertSame(4, $report->groups[0]['count']);
        $this->assertTrue($report->groups[0]['repeated']);
        $this->assertSame(8.0, $report->groups[0]['total_ms']);
        $this->assertSame(['app/Controllers/PostController.php:40'], $report->groups[0]['callers']);
        $this->assertFalse($report->groups[1]['repeated']);
    }

    public function test_exactly_three_repeats_is_not_flagged(): void
    {
        $queries = [['sql' => 'SELECT 1 FROM a WHERE id = 1'], ['sql' => 'SELECT 1 FROM a WHERE id = 2'], ['sql' => 'SELECT 1 FROM a WHERE id = 3']];

        $this->assertSame([], QueryFingerprint::analyze($queries)->alerts);
    }

    public function test_stale_flags_are_overwritten_and_odd_timings_are_tolerated(): void
    {
        $report = QueryFingerprint::analyze([['sql' => 'SELECT 1', 'is_n_plus_1' => true, 'executionMS' => 'unfinished']]);

        $this->assertFalse($report->queries[0]['is_n_plus_1']);
        $this->assertSame(0.0, $report->groups[0]['total_ms']);
        $this->assertSame(0.0, QueryFingerprint::toFloat(null));
        $this->assertSame(1.5, QueryFingerprint::toFloat('1.5'));
    }

    public function test_no_queries_gives_an_empty_report(): void
    {
        $report = QueryFingerprint::analyze([]);

        $this->assertSame([], $report->queries);
        $this->assertSame([], $report->alerts);
        $this->assertSame([], $report->groups);
    }
}
