<?php
namespace Tests\Unit;

use Rhapsody\Core\Container;
use Rhapsody\Core\Proxy\ContainerDecorator;
use Rhapsody\Core\Proxy\LazyProxyFactory;
use Rhapsody\Core\Testing\TestCase;

class ContainerDecoratorTest extends TestCase
{
    private Container $container;
    private LazyProxyFactory $proxyFactory;
    private ContainerDecorator $decorator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container    = new Container();
        $this->proxyFactory = $this->createMock(LazyProxyFactory::class);
        $this->decorator    = new ContainerDecorator(
            $this->container,
            $this->proxyFactory,
            true,
            ['eager_service']
        );
    }

    public function test_resolve_returns_real_instance_when_lazy_disabled(): void
    {
        $decorator = new ContainerDecorator(
            $this->container,
            $this->proxyFactory,
            false,
            []
        );

        $this->container->bind(\stdClass::class, function () {
            return new \stdClass();
        });

        $result = $decorator->resolve(\stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertNotInstanceOf(\Rhapsody\Core\Proxy\LazyProxyInterface::class, $result);
    }

    public function test_resolve_returns_real_instance_when_service_is_eager(): void
    {
        $this->container->bind('eager_service', function () {
            return new \stdClass();
        });

        $result = $this->decorator->resolve('eager_service');
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->proxyFactory->expects($this->never())->method('create');
    }

    public function test_resolve_returns_proxy_when_lazy_enabled_and_not_eager(): void
    {
        $this->container->bind(\stdClass::class, function () {
            return new \stdClass();
        });

        $this->proxyFactory->expects($this->once())
            ->method('create')
            ->with(\stdClass::class)
            ->willReturn(new \stdClass());

        $result = $this->decorator->resolve(\stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $result);
    }

    public function test_resolve_bypasses_proxy_for_controllers(): void
    {
        $controller = new class extends \Rhapsody\Core\BaseController
        {
            public function __construct()
            {}
            public function setContainer(\Rhapsody\Core\Contracts\ContainerInterface $container): void
            {}
            public function getContainer(): \Rhapsody\Core\Contracts\ContainerInterface
            {
                return new \Rhapsody\Core\Container();
            }
        };
        $controllerClass = get_class($controller);

        $this->container->bind($controllerClass, function () use ($controller) {
            return $controller;
        });

        $this->proxyFactory->expects($this->never())->method('create');

        $result = $this->decorator->resolve($controllerClass);
        $this->assertInstanceOf(\Rhapsody\Core\BaseController::class, $result);
    }

    public function test_has_delegates_to_container(): void
    {
        $this->container->bind('test', function () {});
        $this->assertTrue($this->decorator->has('test'));
        $this->assertFalse($this->decorator->has('unknown'));
    }

    public function test_bind_delegates_to_container(): void
    {
        $this->decorator->bind('test', function () {return 'bound';});
        $this->assertTrue($this->container->has('test'));
        $this->assertEquals('bound', $this->container->resolve('test'));
    }

    public function test_instance_delegates_to_container(): void
    {
        $obj = new \stdClass();
        $this->decorator->instance('test', $obj);
        $this->assertTrue($this->container->has('test'));
        $this->assertSame($obj, $this->container->resolve('test'));
    }

    public function test_get_delegates_to_resolve(): void
    {
        // Use a string key (not a class) to avoid proxy creation
        $this->container->bind('test_value', function () {
            return 'value';
        });
        $result = $this->decorator->get('test_value');
        $this->assertEquals('value', $result);
    }

    public function test_static_trace_methods_forwarded(): void
    {
        // Disable lazy loading to ensure trace is recorded without proxy complications
        $decorator = new ContainerDecorator(
            $this->container,
            $this->proxyFactory,
            false,
            []
        );
        Container::resetTrace();
        $decorator->resolve(\stdClass::class);
        $trace = ContainerDecorator::getTrace();
        $this->assertNotEmpty($trace);
        $found = false;
        foreach ($trace as $entry) {
            if (isset($entry['class']) && $entry['class'] === \stdClass::class) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }
}
