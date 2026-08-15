<?php
namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;
use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Ai\Exceptions\AiAuthenticationException;
use Rhapsody\Core\Ai\Exceptions\AiRateLimitException;
use Rhapsody\Core\Ai\Exceptions\AiServerException;
use Rhapsody\Core\Ai\Exceptions\AiTimeoutException;
use Rhapsody\Core\Services\GeminiClient;

class GeminiClientTest extends TestCase
{
    private function makeClient(array $mockResponses, array $config = []): GeminiClient
    {
        $mock = new MockHandler($mockResponses);
        $httpClient = new Client(['handler' => HandlerStack::create($mock)]);
        return new GeminiClient($httpClient, array_merge(['api_key' => 'fake-key-for-test'], $config));
    }

    public function test_it_parses_a_successful_response(): void
    {
        $client = $this->makeClient([
            new Psr7Response(200, [], json_encode([
                'candidates' => [[
                    'content'      => ['parts' => [['text' => 'Hello there!']]],
                    'finishReason' => 'STOP',
                ]],
                'usageMetadata' => ['promptTokenCount' => 5, 'candidatesTokenCount' => 3, 'totalTokenCount' => 8],
            ])),
        ]);

        $response = $client->generateContent('Hi');

        $this->assertSame('Hello there!', $response->getText());
        $this->assertFalse($response->wasTruncated());
        $this->assertFalse($response->wasBlocked());
        $this->assertSame(8, $response->getUsage()['total_tokens']);
    }

    public function test_a_max_tokens_finish_reason_is_flagged_as_truncated_not_thrown(): void
    {
        $client = $this->makeClient([
            new Psr7Response(200, [], json_encode([
                'candidates' => [[
                    'content'      => ['parts' => [['text' => 'This got cut off...']]],
                    'finishReason' => 'MAX_TOKENS',
                ]],
            ])),
        ]);

        $response = $client->generateContent('Write me a novel');

        $this->assertTrue($response->wasTruncated());
        $this->assertSame('This got cut off...', $response->getText(), 'Partial text should still be returned, not discarded.');
    }

    public function test_a_safety_blocked_prompt_is_flagged_as_blocked_not_thrown(): void
    {
        $client = $this->makeClient([
            new Psr7Response(200, [], json_encode([
                'promptFeedback' => ['blockReason' => 'SAFETY'],
            ])),
        ]);

        $response = $client->generateContent('some blocked prompt');

        $this->assertTrue($response->wasBlocked());
    }

    public function test_a_missing_api_key_throws_authentication_exception_without_a_request(): void
    {
        $client = new GeminiClient(new Client(), ['api_key' => '']);

        $this->expectException(AiAuthenticationException::class);
        $client->generateContent('hi');
    }

    public function test_a_401_response_throws_authentication_exception_with_the_provider_message(): void
    {
        $client = $this->makeClient([
            new RequestException(
                '401',
                new Psr7Request('POST', 'x'),
                new Psr7Response(401, [], json_encode(['error' => ['message' => 'API key invalid']]))
            ),
        ]);

        try {
            $client->generateContent('hi');
            $this->fail('Expected AiAuthenticationException was not thrown.');
        } catch (AiAuthenticationException $e) {
            $this->assertStringContainsString('API key invalid', $e->getMessage());
        }
    }

    public function test_a_429_response_throws_rate_limit_exception_with_retry_after(): void
    {
        $client = $this->makeClient([
            new RequestException(
                '429',
                new Psr7Request('POST', 'x'),
                new Psr7Response(429, ['Retry-After' => '30'], json_encode(['error' => ['message' => 'Quota exceeded']]))
            ),
        ]);

        try {
            $client->generateContent('hi');
            $this->fail('Expected AiRateLimitException was not thrown.');
        } catch (AiRateLimitException $e) {
            $this->assertSame(30, $e->retryAfter);
        }
    }

    public function test_a_500_response_throws_server_exception(): void
    {
        $client = $this->makeClient([
            new RequestException(
                '500',
                new Psr7Request('POST', 'x'),
                new Psr7Response(500, [], json_encode(['error' => ['message' => 'Internal error']]))
            ),
        ]);

        $this->expectException(AiServerException::class);
        $client->generateContent('hi');
    }

    public function test_a_connect_timeout_throws_timeout_exception(): void
    {
        $client = $this->makeClient([
            new ConnectException('Connection timed out', new Psr7Request('POST', 'x')),
        ]);

        $this->expectException(AiTimeoutException::class);
        $client->generateContent('hi');
    }

    public function test_a_read_timeout_with_no_response_throws_timeout_exception(): void
    {
        // Simulates a request that was sent but never got a response in
        // time — Guzzle surfaces this as a RequestException (not always
        // ConnectException) with no response and a cURL errno of 28.
        $client = $this->makeClient([
            new RequestException(
                'cURL error 28: Operation timed out after 60000 milliseconds',
                new Psr7Request('POST', 'x'),
                null,
                null,
                ['errno' => CURLE_OPERATION_TIMEDOUT]
            ),
        ]);

        $this->expectException(AiTimeoutException::class);
        $client->generateContent('hi');
    }
}
