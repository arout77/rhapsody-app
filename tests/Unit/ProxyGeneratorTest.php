<?php
namespace Tests\Unit;

use Rhapsody\Core\Proxy\ProxyGenerator;
use Rhapsody\Core\Testing\TestCase;

interface DummyTestInterface
{
    public function testMethod(): void;
}

final class DummyFinalClass
{}

class ProxyGeneratorTest extends TestCase
{
    private string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheDir = sys_get_temp_dir() . '/rhapsody-proxy-test';
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

    public function test_generate_creates_proxy_class_for_concrete_class(): void
    {
        $generator = new ProxyGenerator($this->cacheDir);

        $proxyClass = $generator->generate(\stdClass::class);
        $this->assertStringStartsWith('Rhapsody\\Core\\Proxy\\Generated\\', $proxyClass);
        $this->assertTrue(class_exists($proxyClass));

        // Check file exists
        $hash         = md5(\stdClass::class);
        $expectedFile = $this->cacheDir . '/' . $hash . '_stdClassProxy.php';
        $this->assertFileExists($expectedFile);
    }

    public function test_generate_creates_proxy_for_interface(): void
    {
        $generator = new ProxyGenerator($this->cacheDir);

        $proxyClass = $generator->generate(DummyTestInterface::class);
        $this->assertStringStartsWith('Rhapsody\\Core\\Proxy\\Generated\\', $proxyClass);
        $this->assertTrue(class_exists($proxyClass));

        $hash         = md5(DummyTestInterface::class);
        $expectedFile = $this->cacheDir . '/' . $hash . '_DummyTestInterfaceProxy.php';
        $this->assertFileExists($expectedFile);
    }

    public function test_generate_proxy_extends_original_class(): void
    {
        $generator = new ProxyGenerator($this->cacheDir);

        $proxyClass = $generator->generate(\stdClass::class);
        $this->assertTrue(is_subclass_of($proxyClass, \stdClass::class));
    }

    public function test_generate_proxy_implements_lazy_interface(): void
    {
        $generator = new ProxyGenerator($this->cacheDir);

        $proxyClass = $generator->generate(\stdClass::class);
        $this->assertTrue(is_subclass_of($proxyClass, \Rhapsody\Core\Proxy\LazyProxyInterface::class));
    }

    public function test_generate_throws_exception_for_final_class(): void
    {
        $generator = new ProxyGenerator($this->cacheDir);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot proxy final class');
        $generator->generate(DummyFinalClass::class);
    }

    public function test_generated_proxy_has_constructor_that_accepts_closure(): void
    {
        $generator = new ProxyGenerator($this->cacheDir);

        $proxyClass  = $generator->generate(\stdClass::class);
        $reflection  = new \ReflectionClass($proxyClass);
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);
        $params = $constructor->getParameters();
        $this->assertCount(1, $params);
        $this->assertInstanceOf(\ReflectionNamedType::class, $params[0]->getType());
        $this->assertEquals(\Closure::class, $params[0]->getType()->getName());
    }
}
