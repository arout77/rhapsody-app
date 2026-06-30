<?php
namespace Tests\Unit;

use Rhapsody\Core\Container;
use Rhapsody\Core\Proxy\LazyProxyFactory;
use Rhapsody\Core\Testing\TestCase;

class TestServiceForProxy
{
    public bool $called = false;
    public function testMethod(): void
    {
        $this->called = true;
    }
}

class LazyProxyFactoryTest extends TestCase
{
    private Container $container;
    private LazyProxyFactory $factory;
    private string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->cacheDir  = sys_get_temp_dir() . '/rhapsody-proxy-test';
        if (! is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        $this->factory = new LazyProxyFactory($this->container, $this->cacheDir);
    }

    protected function tearDown(): void
    {
        $files = glob($this->cacheDir . '/*.php');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($this->cacheDir);
    }

    public function test_create_returns_proxy_object(): void
    {
        $this->container->bind(\stdClass::class, function () {
            return new \stdClass();
        });

        $proxy = $this->factory->create(\stdClass::class);
        $this->assertIsObject($proxy);
        $this->assertInstanceOf(\Rhapsody\Core\Proxy\LazyProxyInterface::class, $proxy);
    }

    public function test_proxy_initializes_real_object_on_method_call(): void
    {
        $this->container->bind(TestServiceForProxy::class, function () {
            return new TestServiceForProxy();
        });

        $proxy = $this->factory->create(TestServiceForProxy::class);

        // Ensure not initialized yet
        $reflection  = new \ReflectionClass($proxy);
        $wrappedProp = $reflection->getProperty('wrappedObject');
        $wrappedProp->setAccessible(true);
        $this->assertNull($wrappedProp->getValue($proxy));

        // Call method, should initialize
        $proxy->testMethod();

        // Now the wrapped object should be the real service and the method called
        $wrapped = $wrappedProp->getValue($proxy);
        $this->assertInstanceOf(TestServiceForProxy::class, $wrapped);
        $this->assertTrue($wrapped->called);
    }

    public function test_proxy_marks_trace_as_proxy(): void
    {
        $this->container->bind(\stdClass::class, function () {
            return new \stdClass();
        });

        Container::resetTrace();
        $proxy = $this->factory->create(\stdClass::class);
        $proxy->getWrappedObject();

        $trace = Container::getTrace();
        $this->assertNotEmpty($trace);
        $found = false;
        foreach ($trace as $entry) {
            if (isset($entry['class']) && $entry['class'] === \stdClass::class) {
                $this->assertArrayHasKey('proxy', $entry);
                $this->assertTrue($entry['proxy']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Proxy flag not found in trace');
    }

    public function test_proxy_generator_creates_cache_file(): void
    {
        $this->container->bind(\stdClass::class, function () {
            return new \stdClass();
        });

        $this->factory->create(\stdClass::class);

        // Compute the expected filename
        $hash         = md5(\stdClass::class);
        $expectedFile = $this->cacheDir . '/' . $hash . '_stdClassProxy.php';
        $this->assertFileExists($expectedFile);
    }
}
