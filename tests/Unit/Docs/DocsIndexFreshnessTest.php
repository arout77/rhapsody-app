<?php
declare(strict_types=1);

namespace Tests\Unit\Docs;

use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Ai\Docs\DocsIndex;
use Rhapsody\Core\Ai\Docs\DocsIndexBuilder;

/**
 * The docs index ships inside the package, so it must be rebuilt whenever the
 * docs (or the chunker, routes, controller that shape it) change.
 */
class DocsIndexFreshnessTest extends TestCase
{
    private function builder(): DocsIndexBuilder
    {
        return new DocsIndexBuilder(dirname((string) (new \ReflectionClass(DocsIndex::class))->getFileName(), 4));
    }

    public function test_the_committed_index_matches_the_docs_sources(): void
    {
        $this->assertTrue(
            $this->builder()->isFresh(),
            'resources/docs-index/chunks.json is stale. Run: php rhapsody docs:build'
        );
    }

    public function test_the_committed_index_loads_and_answers_a_real_question(): void
    {
        $index = DocsIndex::fromFile(DocsIndex::defaultPath());

        $this->assertGreaterThan(100, $index->count());
        $this->assertNotEmpty($index->search('how do I create a controller')->hits);
    }
}
