<?php
declare(strict_types=1);

namespace Tests\Unit\Docs;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as Psr7Response;
use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Ai\AiResponse;
use Rhapsody\Core\Ai\Docs\AskService;
use Rhapsody\Core\Ai\Docs\DocsIndex;
use Rhapsody\Core\Ai\Exceptions\AiAuthenticationException;
use Rhapsody\Core\Ai\Exceptions\AiException;
use Rhapsody\Core\Ai\Exceptions\AiRateLimitException;
use Rhapsody\Core\Contracts\AiClientInterface;
use Rhapsody\Core\Services\GeminiClient;

class AskServiceTest extends TestCase
{
    private const QUESTION = 'how do I attach middleware to a route';

    private string $logPath = '';

    protected function setUp(): void
    {
        $this->logPath = sys_get_temp_dir() . '/rh_gaps_' . bin2hex(random_bytes(4)) . '/doc-gaps.jsonl';
    }

    protected function tearDown(): void
    {
        if (is_file($this->logPath)) {
            unlink($this->logPath);
        }
        if (is_dir(dirname($this->logPath))) {
            rmdir(dirname($this->logPath));
        }
    }

    private function response(string $text, string $finish = 'STOP'): AiResponse
    {
        return AiResponse::fromGeminiPayload([
            'candidates' => [['content' => ['parts' => [['text' => $text]]], 'finishReason' => $finish]],
        ]);
    }

    /**
     * A recording fake: returns $reply (or throws it) and keeps what it was sent.
     *
     * @param AiResponse|\Throwable $reply
     */
    private function fake(AiResponse|\Throwable $reply): object
    {
        return new class ($reply) implements AiClientInterface {
            public int $calls = 0;
            public string $prompt = '';
            /** @var array<string, mixed> */
            public array $options = [];

            public function __construct(private readonly AiResponse|\Throwable $reply)
            {
            }

            public function generateContent(string $prompt, array $options = []): AiResponse
            {
                $this->calls++;
                $this->prompt  = $prompt;
                $this->options = $options;
                if ($this->reply instanceof \Throwable) {
                    throw $this->reply;
                }

                return $this->reply;
            }

            public function generateChatContent(array $messages, array $options = []): AiResponse
            {
                throw new \LogicException('not used');
            }
        };
    }

    private function service(?AiClientInterface $client, bool $allowed = true, bool $log = true): AskService
    {
        return new AskService(
            new DocsIndex(DocsIndexTest::corpus()),
            $client === null ? null : static fn (): AiClientInterface => $client,
            $allowed,
            $log ? $this->logPath : null,
        );
    }

    public function test_retrieval_only_when_no_client_exists(): void
    {
        $result = $this->service(null)->ask(self::QUESTION);

        $this->assertNull($result->answer);
        $this->assertNotEmpty($result->hits);
        $this->assertSame('routing#middleware', $result->hits[0]->chunk->id);
        $this->assertNotEmpty($result->notices);
    }

    public function test_the_client_is_never_called_with_no_ai(): void
    {
        $client = $this->fake($this->response('Use ->middleware() [1].'));

        $result = $this->service($client)->ask(self::QUESTION, useAi: false);

        $this->assertSame(0, $client->calls);
        $this->assertNull($result->answer);
        $this->assertNotEmpty($result->hits);
    }

    public function test_the_client_is_never_called_outside_development(): void
    {
        $client = $this->fake($this->response('Use ->middleware() [1].'));

        $result = $this->service($client, allowed: false)->ask(self::QUESTION);

        $this->assertSame(0, $client->calls);
        $this->assertNull($result->answer);
        $this->assertStringContainsString('development', implode(' ', $result->notices));
    }

    public function test_a_cited_answer_is_returned_and_the_system_prompt_is_sent(): void
    {
        $client = $this->fake($this->response('Call ->middleware() on the route [1].'));

        $result = $this->service($client)->ask(self::QUESTION);

        $this->assertSame('Call ->middleware() on the route [1].', $result->answer);
        $this->assertSame([1], $result->cited);
        $this->assertSame(1, $client->calls);
        $this->assertSame(AskService::SYSTEM_PROMPT, $client->options['system']);
        $this->assertStringContainsString('[1] Routing › Middleware (/docs/routing)', $client->prompt);
        $this->assertStringContainsString('Question: ' . self::QUESTION, $client->prompt);
    }

    public function test_only_question_and_docs_text_are_sent_to_the_model(): void
    {
        $client = $this->fake($this->response('See [1].'));

        $this->service($client)->ask(self::QUESTION);

        $this->assertStringNotContainsString('api_key', $client->prompt);
        $this->assertStringStartsWith('Documentation sections:', $client->prompt);
    }

    public function test_an_answer_citing_a_section_that_was_not_provided_is_discarded(): void
    {
        $client = $this->fake($this->response('Do it like this [1] and also [9].'));

        $result = $this->service($client)->ask(self::QUESTION);

        $this->assertNull($result->answer);
        $this->assertNotEmpty($result->hits);
        $this->assertStringContainsString('discarded', implode(' ', $result->notices));
    }

    public function test_an_answer_with_no_citations_is_discarded(): void
    {
        $client = $this->fake($this->response('Just use middleware, trust me.'));

        $result = $this->service($client)->ask(self::QUESTION);

        $this->assertNull($result->answer);
        $this->assertSame([], $result->cited);
    }

    public function test_array_indexes_inside_code_are_not_mistaken_for_citations(): void
    {
        $client = $this->fake($this->response("Read it with `\$items[7]` and see [1].\n```php\n\$x = \$list[12];\n```"));

        $result = $this->service($client)->ask(self::QUESTION);

        $this->assertNotNull($result->answer);
        $this->assertSame([1], $result->cited);
    }

    public function test_not_in_docs_falls_back_and_records_a_gap(): void
    {
        $client = $this->fake($this->response('NOT_IN_DOCS'));

        $result = $this->service($client)->ask(self::QUESTION);

        $this->assertNull($result->answer);
        $this->assertNotNull($result->gapReason);
        $this->assertFileExists($this->logPath);
        $row = json_decode((string) file_get_contents($this->logPath), true);
        $this->assertSame(self::QUESTION, $row['question']);
    }

    public function test_every_ai_failure_degrades_to_retrieval_only(): void
    {
        $failures = [
            new AiAuthenticationException('Gemini key rejected'),
            new AiRateLimitException('slow down', 30),
            new AiException('boom'),
        ];

        foreach ($failures as $failure) {
            $result = $this->service($this->fake($failure), log: false)->ask(self::QUESTION);

            $this->assertNull($result->answer);
            $this->assertNotEmpty($result->hits);
            $this->assertNotEmpty($result->notices);
        }
    }

    public function test_a_rate_limit_notice_includes_the_retry_delay(): void
    {
        $result = $this->service($this->fake(new AiRateLimitException('slow down', 30)))->ask(self::QUESTION);

        $this->assertStringContainsString('30', implode(' ', $result->notices));
    }

    public function test_a_provider_that_cannot_be_built_degrades_instead_of_throwing(): void
    {
        $service = new AskService(
            new DocsIndex(DocsIndexTest::corpus()),
            static function (): AiClientInterface {
                throw new AiException('unknown provider');
            },
        );

        $result = $service->ask(self::QUESTION);

        $this->assertNull($result->answer);
        $this->assertNotEmpty($result->hits);
    }

    public function test_blocked_and_empty_responses_are_not_shown_as_answers(): void
    {
        $blocked = AiResponse::fromGeminiPayload(['promptFeedback' => ['blockReason' => 'SAFETY']]);

        $this->assertNull($this->service($this->fake($blocked))->ask(self::QUESTION)->answer);
        $this->assertNull($this->service($this->fake($this->response('   ')))->ask(self::QUESTION)->answer);
    }

    public function test_a_truncated_answer_still_shows_with_a_notice(): void
    {
        $client = $this->fake($this->response('Call ->middleware() on the route [1], then', 'MAX_TOKENS'));

        $result = $this->service($client)->ask(self::QUESTION);

        $this->assertNotNull($result->answer);
        $this->assertStringContainsString('cut off', implode(' ', $result->notices));
    }

    public function test_a_weak_match_is_logged_as_a_docs_gap_without_calling_the_ai(): void
    {
        $client = $this->fake($this->response('See [1].'));

        $result = $this->service($client)->ask('quantum blockchain kubernetes orchestration', useAi: false);

        $this->assertNotNull($result->gapReason);
        $this->assertFileExists($this->logPath);
    }

    public function test_a_confident_match_is_not_logged(): void
    {
        $this->service(null)->ask(self::QUESTION);

        $this->assertFalse(is_file($this->logPath));
    }

    public function test_the_real_gemini_client_sends_the_system_prompt_as_a_system_instruction(): void
    {
        $history = [];
        $mock    = new MockHandler([new Psr7Response(200, [], json_encode([
            'candidates' => [['content' => ['parts' => [['text' => 'Use ->middleware() [1].']]], 'finishReason' => 'STOP']],
        ]) ?: '')]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $gemini = new GeminiClient(new Client(['handler' => $stack]), ['api_key' => 'fake-key-for-test']);

        $result = $this->service($gemini)->ask(self::QUESTION);

        $this->assertSame([1], $result->cited);
        $this->assertCount(1, $history);
        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame(AskService::SYSTEM_PROMPT, $body['systemInstruction']['parts'][0]['text']);
    }
}
