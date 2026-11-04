<?php
declare(strict_types=1);

namespace Tests\Unit\Docs;

use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Ai\Docs\DocChunk;
use Rhapsody\Core\Ai\Docs\DocsChunker;

class DocsChunkerTest extends TestCase
{
    private const PAGE = <<<'TWIG'
{% extends "@core/docs/_layout.twig" %}
{% block title %}Routing{% endblock %}
{% block doc_content %}
<h1>Routing</h1>
<p>Routes map URLs to controllers. Visit <a href="{{ base_url }}/docs">the docs</a> for more.</p>

<h2 id="basic">Basic routes</h2>
<p>Register a route with the static router so requests reach your controller.</p>
<pre><code>{% verbatim %}Router::get('/users/{id}', [UserController::class, 'show']);{% endverbatim %}</code></pre>

<h2 id="mw">Middleware</h2>
{# a template comment that must never reach the index #}
<p>Attach middleware to a single route with the middleware method.</p>
<ul><li>First item in the list</li><li>Second item in the list</li></ul>
<h3>Ordering</h3>
<p>Middleware runs in the order it is attached to the route definition.</p>
{% endblock %}
TWIG;

    /**
     * @return list<DocChunk>
     */
    private function chunk(string $twig = self::PAGE): array
    {
        return (new DocsChunker())->chunkSource('routing', '/docs/routing', $twig);
    }

    /**
     * @param list<DocChunk> $chunks
     */
    private function find(array $chunks, string $needle): DocChunk
    {
        foreach ($chunks as $chunk) {
            if (str_contains($chunk->text, $needle)) {
                return $chunk;
            }
        }
        $this->fail("No chunk contains '$needle'");
    }

    public function test_it_splits_a_page_on_h2_and_h3_and_keeps_the_h1_as_the_page_title(): void
    {
        $chunks = $this->chunk();

        $this->assertNotEmpty($chunks);
        foreach ($chunks as $chunk) {
            $this->assertSame('Routing', $chunk->pageTitle);
            $this->assertSame('routing', $chunk->page);
            $this->assertSame('/docs/routing', $chunk->url);
        }

        $ordering = $this->find($chunks, 'runs in the order');
        $this->assertSame(['Middleware', 'Ordering'], $ordering->headingPath);
        $this->assertSame('Routing › Middleware › Ordering', $ordering->label());
    }

    public function test_verbatim_content_is_kept_and_fenced_as_code(): void
    {
        $chunk = $this->find($this->chunk(), 'Router::get');

        $this->assertStringContainsString("```", $chunk->text);
        $this->assertStringContainsString("Router::get('/users/{id}', [UserController::class, 'show']);", $chunk->text);
        $this->assertStringNotContainsString('verbatim', $chunk->text);
    }

    public function test_twig_syntax_and_comments_never_reach_the_text(): void
    {
        foreach ($this->chunk() as $chunk) {
            $this->assertStringNotContainsString('{{', $chunk->text);
            $this->assertStringNotContainsString('{%', $chunk->text);
            $this->assertStringNotContainsString('{#', $chunk->text);
            $this->assertStringNotContainsString('template comment', $chunk->text);
            $this->assertStringNotContainsString('<p>', $chunk->text);
        }
    }

    public function test_lists_are_flattened_to_readable_lines(): void
    {
        $chunk = $this->find($this->chunk(), 'First item');

        $this->assertStringContainsString('First item in the list', $chunk->text);
        $this->assertStringContainsString('Second item in the list', $chunk->text);
    }

    public function test_chunk_ids_are_unique_and_stable(): void
    {
        $first  = array_map(static fn (DocChunk $c): string => $c->id, $this->chunk());
        $second = array_map(static fn (DocChunk $c): string => $c->id, $this->chunk());

        $this->assertSame($first, $second);
        $this->assertSame(count($first), count(array_unique($first)));
        foreach ($first as $id) {
            $this->assertStringStartsWith('routing#', $id);
        }
    }

    public function test_literal_twig_expressions_keep_their_text(): void
    {
        $twig = "{% block doc_content %}<h1>T</h1><h2>Curly</h2><p>Use {{ '{{ name }}' }} to print a variable in a template.</p>{% endblock %}";

        $chunk = $this->find($this->chunk($twig), 'to print a variable');

        $this->assertStringContainsString('{{ name }}', $chunk->text);
    }

    public function test_invalid_curly_entities_used_in_the_docs_become_braces(): void
    {
        $twig = '{% block doc_content %}<h1>T</h1><h2>Entities</h2><p>Write &lcub;&lcub; value &rcub;&rcub; in the template body.</p>{% endblock %}';

        $chunk = $this->find($this->chunk($twig), 'in the template body');

        $this->assertStringContainsString('{{ value }}', $chunk->text);
        $this->assertStringNotContainsString('&lc', $chunk->text);
    }

    public function test_a_long_section_is_split_on_paragraph_boundaries(): void
    {
        $paragraphs = '';
        for ($i = 1; $i <= 12; $i++) {
            $paragraphs .= "<p>Paragraph number $i " . str_repeat('lorem ipsum dolor ', 12) . "</p>\n";
        }
        $twig = "{% block doc_content %}<h1>Long</h1><h2>Big section</h2>$paragraphs{% endblock %}";

        $chunks = (new DocsChunker(maxChars: 600))->chunkSource('long', '/docs/long', $twig);

        $this->assertGreaterThan(1, count($chunks));
        foreach ($chunks as $chunk) {
            $this->assertSame(['Big section'], $chunk->headingPath);
            $this->assertTrue(strlen($chunk->text) <= 900, 'chunk grew far past the limit');
        }
    }

    public function test_layout_partials_are_skipped_when_chunking_a_directory(): void
    {
        $dir = sys_get_temp_dir() . '/rh_chunker_' . bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents($dir . '/_layout.twig', '{% block doc_content %}<h1>L</h1><h2>Layout</h2><p>Should not be indexed at all.</p>{% endblock %}');
        file_put_contents($dir . '/routing.twig', self::PAGE);

        try {
            $chunks = (new DocsChunker())->chunkDirectory($dir, new \Rhapsody\Core\Ai\Docs\DocsUrlMap());
        } finally {
            array_map('unlink', glob($dir . '/*') ?: []);
            rmdir($dir);
        }

        $this->assertNotEmpty($chunks);
        foreach ($chunks as $chunk) {
            $this->assertSame('routing', $chunk->page);
        }
    }
}
