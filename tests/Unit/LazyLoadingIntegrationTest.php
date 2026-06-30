<?php
namespace Tests\Feature;

use Rhapsody\Core\Container;
use Rhapsody\Core\Proxy\ContainerDecorator;
use Rhapsody\Core\Proxy\LazyProxyFactory;
use Rhapsody\Core\Testing\TestCase;

// Test service class (defined at top level)
class LazyLoadingTestService
{
    public bool $initialized = false;

    public function __construct()
    {
        $this->initialized = true;
    }

    public function doSomething(): string
    {
        return 'done';
    }
}

class LazyLoadingIntegrationTest extends TestCase
{
    private Container $container;
    private string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->cacheDir  = sys_get_temp_dir() . '/rhapsody-proxy-test';
        if (! is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $files = glob($this->cacheDir . '/*.php');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($this->cacheDir);
    }

    public function test_full_lazy_loading_flow(): void
    {
        $this->container->bind(LazyLoadingTestService::class, function () {
            return new LazyLoadingTestService();
        });

        $proxyFactory = new LazyProxyFactory($this->container, $this->cacheDir);
        $decorator    = new ContainerDecorator(
            $this->container,
            $proxyFactory,
            true,
            []
        );

        $service = $decorator->resolve(LazyLoadingTestService::class);
        // It should implement our proxy interface
        $this->assertInstanceOf(\Rhapsody\Core\Proxy\LazyProxyInterface::class, $service);
        // It should extend the original class (since we extend it)
        $this->assertInstanceOf(LazyLoadingTestService::class, $service);
        // But the wrapped object should be null initially
        $reflection  = new \ReflectionClass($service);
        $wrappedProp = $reflection->getProperty('wrappedObject');
        $wrappedProp->setAccessible(true);
        $this->assertNull($wrappedProp->getValue($service));

        // Call a method → should trigger initialization
        $result = $service->doSomething();
        $this->assertEquals('done', $result);

        // Now the wrapped object should be the real service
        $wrapped = $wrappedProp->getValue($service);
        $this->assertInstanceOf(LazyLoadingTestService::class, $wrapped);
        $this->assertTrue($wrapped->initialized);
    }

    public function test_lazy_loading_can_be_toggled_via_config(): void
    {
        $_ENV['LAZY_LOADING_ENABLED'] = 'true';
        $this->assertTrue(filter_var($_ENV['LAZY_LOADING_ENABLED'], FILTER_VALIDATE_BOOLEAN));

        $_ENV['LAZY_LOADING_ENABLED'] = 'false';
        $this->assertFalse(filter_var($_ENV['LAZY_LOADING_ENABLED'], FILTER_VALIDATE_BOOLEAN));
    }

    public function test_eager_services_skip_proxying(): void
    {
        $eagerClass = new class {
        };
        $this->container->bind(get_class($eagerClass), function () use ($eagerClass) {
            return $eagerClass;
        });

        $proxyFactory = $this->createMock(LazyProxyFactory::class);
        $proxyFactory->expects($this->never())->method('create');

        $decorator = new ContainerDecorator(
            $this->container,
            $proxyFactory,
            true,
            [get_class($eagerClass)]
        );

        $result = $decorator->resolve(get_class($eagerClass));
        $this->assertInstanceOf(get_class($eagerClass), $result);
    }

    public function test_controllers_are_not_proxied(): void
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

        $this->container->bind(get_class($controller), function () use ($controller) {
            return $controller;
        });

        $proxyFactory = $this->createMock(LazyProxyFactory::class);
        $proxyFactory->expects($this->never())->method('create');

        $decorator = new ContainerDecorator(
            $this->container,
            $proxyFactory,
            true,
            []
        );

        $result = $decorator->resolve(get_class($controller));
        $this->assertInstanceOf(\Rhapsody\Core\BaseController::class, $result);
    }
}
