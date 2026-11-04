<?php
declare(strict_types=1);

namespace Tests\Unit\Docs;

use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Ai\Docs\AskServiceFactory;
use Rhapsody\Core\Container;

class AskServiceFactoryTest extends TestCase
{
    private const QUESTION = 'how do I attach middleware to a route';

    /**
     * @param array<string, mixed> $config
     */
    private function container(array $config): Container
    {
        $container = new Container();
        $container->instance('config', $config);

        return $container;
    }

    public function test_ai_is_only_allowed_in_development(): void
    {
        $this->assertTrue(AskServiceFactory::aiAllowed($this->container(['app_env' => 'development'])));
        $this->assertFalse(AskServiceFactory::aiAllowed($this->container(['app_env' => 'production'])));
        $this->assertFalse(AskServiceFactory::aiAllowed($this->container([])));
    }

    public function test_it_answers_from_the_bundled_docs_with_no_key_and_no_binding(): void
    {
        $container = $this->container(['app_env' => 'development', 'ai' => ['provider' => 'gemini', 'gemini' => ['api_key' => '']]]);

        $result = AskServiceFactory::fromContainer($container, null)->ask(self::QUESTION);

        $this->assertNull($result->answer);
        $this->assertNotEmpty($result->hits);
        $this->assertStringContainsString('API key', implode(' ', $result->notices));
    }

    public function test_an_unknown_provider_degrades_to_the_docs_instead_of_throwing(): void
    {
        $container = $this->container(['app_env' => 'development', 'ai' => ['provider' => 'nope']]);

        $result = AskServiceFactory::fromContainer($container, null)->ask(self::QUESTION);

        $this->assertNull($result->answer);
        $this->assertNotEmpty($result->hits);
    }

    public function test_nothing_leaves_the_machine_outside_development(): void
    {
        $container = $this->container(['app_env' => 'production', 'ai' => ['gemini' => ['api_key' => 'would-be-used']]]);

        $result = AskServiceFactory::fromContainer($container, null)->ask(self::QUESTION);

        $this->assertNull($result->answer);
        $this->assertStringContainsString('development', implode(' ', $result->notices));
    }
}
