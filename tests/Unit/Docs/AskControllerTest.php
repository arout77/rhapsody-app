<?php
declare(strict_types=1);

namespace Tests\Unit\Docs;

use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Container;
use Rhapsody\Core\Contracts\ContainerInterface;
use Rhapsody\Core\Controllers\AskController;
use Rhapsody\Core\Exceptions\HttpException;
use Rhapsody\Core\Request;

/**
 * The browser page is a development tool: outside app_env "development" both
 * actions must 404. The controller is built without its constructor because
 * BaseController needs a full application (database, Twig); only the
 * container the guard reads from is injected.
 */
class AskControllerTest extends TestCase
{
    /**
     * @param array<string, mixed> $config
     */
    private function controller(array $config): AskController
    {
        $container = new Container();
        $container->instance('config', $config);

        $controller = (new \ReflectionClass(AskController::class))->newInstanceWithoutConstructor();
        (function (ContainerInterface $container): void {
            $this->container = $container;
        })->bindTo($controller, AskController::class)($container);

        return $controller;
    }

    public function test_the_page_is_a_404_outside_development(): void
    {
        foreach (['production', 'staging', ''] as $env) {
            try {
                $this->controller(['app_env' => $env])->index(Request::create('GET', '/chat'));
                $this->fail("index() was reachable with app_env '$env'");
            } catch (HttpException $e) {
                $this->assertSame(404, $e->getStatusCode());
            }
        }
    }

    public function test_the_query_endpoint_is_a_404_outside_development(): void
    {
        try {
            $this->controller(['app_env' => 'production'])->query(Request::create('POST', '/chat/query', ['question' => 'attach middleware to a route']));
            $this->fail('query() was reachable in production');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public function test_the_query_endpoint_answers_from_the_docs_in_development(): void
    {
        $controller = $this->controller(['app_env' => 'development', 'ai' => ['gemini' => ['api_key' => '']]]);

        $response = $controller->query(Request::create('POST', '/chat/query', ['question' => 'attach middleware to a route']));

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertSame('attach middleware to a route', $body['question']);
        $this->assertNotEmpty($body['sources']);
    }

    public function test_an_empty_or_oversized_question_is_rejected(): void
    {
        $controller = $this->controller(['app_env' => 'development']);

        $this->assertSame(400, $controller->query(Request::create('POST', '/chat/query', ['question' => '   ']))->getStatusCode());
        $this->assertSame(400, $controller->query(Request::create('POST', '/chat/query', ['question' => str_repeat('a', 501)]))->getStatusCode());
    }
}
