<?php
declare(strict_types=1);

namespace Tests\Unit\Docs;

use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Ai\Docs\DocChunk;
use Rhapsody\Core\Ai\Docs\DocsIndex;

class DocsIndexTest extends TestCase
{
    /**
     * @return list<DocChunk>
     */
    public static function corpus(): array
    {
        return [
            new DocChunk('routing#middleware', 'routing', '/docs/routing', 'Routing', ['Middleware'],
                "Attach middleware to a route with the middleware method.\n```php\nRouter::get('/admin', [AdminController::class, 'index'])->middleware(AuthMiddleware::class);\n```"),
            new DocChunk('routing#basic', 'routing', '/docs/routing', 'Routing', ['Basic routes'],
                'Register a route with Router::get so a URL reaches a controller action.'),
            new DocChunk('database#queries', 'database', '/docs/database', 'Database', ['Queries'],
                'Use the entity manager to run a query and fetch entities from the database.'),
            new DocChunk('cache#redis', 'cache', '/docs/cache', 'Cache', ['Redis driver'],
                'Configure the redis cache driver and set a time to live for each key.'),
            new DocChunk('console#make', 'console', '/docs/console', 'Console', ['Generating middleware'],
                'Run make:middleware to generate a middleware class in src/Middleware.'),
        ];
    }

    public function test_the_best_section_for_a_question_ranks_first(): void
    {
        $result = (new DocsIndex(self::corpus()))->search('how do I attach middleware to a route');

        $this->assertNotEmpty($result->hits);
        $this->assertSame('routing#middleware', $result->hits[0]->chunk->id);
        $this->assertTrue($result->confident);
    }

    public function test_code_identifiers_are_searchable(): void
    {
        $result = (new DocsIndex(self::corpus()))->search('AuthMiddleware');

        $this->assertSame('routing#middleware', $result->hits[0]->chunk->id);
    }

    public function test_camel_case_and_snake_case_are_split_into_words(): void
    {
        $result = (new DocsIndex(self::corpus()))->search('time_to_live redis');

        $this->assertSame('cache#redis', $result->hits[0]->chunk->id);
    }

    public function test_plurals_match_singular_text(): void
    {
        $result = (new DocsIndex(self::corpus()))->search('which entities does a query return');

        $this->assertSame('database#queries', $result->hits[0]->chunk->id);
    }

    public function test_heading_terms_outweigh_body_terms(): void
    {
        $result = (new DocsIndex(self::corpus()))->search('generating middleware');

        $this->assertSame('console#make', $result->hits[0]->chunk->id);
    }

    public function test_nonsense_is_not_confident(): void
    {
        $result = (new DocsIndex(self::corpus()))->search('quantum blockchain kubernetes orchestration');

        $this->assertFalse($result->confident);
    }

    public function test_a_question_made_only_of_stopwords_returns_nothing(): void
    {
        $result = (new DocsIndex(self::corpus()))->search('how do I the a of');

        $this->assertSame([], $result->hits);
        $this->assertFalse($result->confident);
    }

    public function test_the_limit_caps_the_number_of_hits(): void
    {
        $result = (new DocsIndex(self::corpus()))->search('middleware route controller', 2);

        $this->assertTrue(count($result->hits) <= 2);
    }

    public function test_an_empty_index_is_safe(): void
    {
        $index = new DocsIndex([]);

        $this->assertSame(0, $index->count());
        $this->assertSame([], $index->search('anything at all')->hits);
    }

    public function test_hits_report_which_terms_matched(): void
    {
        $hit = (new DocsIndex(self::corpus()))->search('redis driver')->hits[0];

        $this->assertSame('cache#redis', $hit->chunk->id);
        $this->assertContains('redis', $hit->matchedTerms);
        $this->assertGreaterThan(0.0, $hit->score);
    }
}
