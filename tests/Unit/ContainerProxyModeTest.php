<?php
namespace Tests\Unit;

use Rhapsody\Core\Container;
use Rhapsody\Core\Testing\TestCase;

class ContainerProxyModeTest extends TestCase
{
    public function test_setProxyMode_marks_trace_entries(): void
    {
        $container = new Container();

        $container->bind(\stdClass::class, function () {
            return new \stdClass();
        });

        Container::resetTrace();
        Container::setProxyMode(true);
        $container->resolve(\stdClass::class);
        Container::setProxyMode(false);

        $trace = Container::getTrace();
        $this->assertNotEmpty($trace);
        $entry = $trace[0];
        $this->assertArrayHasKey('proxy', $entry);
        $this->assertTrue($entry['proxy']);
        $this->assertEquals(\stdClass::class, $entry['class']);
    }

    public function test_setProxyMode_false_does_not_add_proxy_flag(): void
    {
        $container = new Container();
        $container->bind(\stdClass::class, function () {
            return new \stdClass();
        });

        Container::resetTrace();
        Container::setProxyMode(false);
        $container->resolve(\stdClass::class);

        $trace = Container::getTrace();
        $this->assertNotEmpty($trace);
        $entry = $trace[0];
        $this->assertArrayNotHasKey('proxy', $entry);
    }
}
