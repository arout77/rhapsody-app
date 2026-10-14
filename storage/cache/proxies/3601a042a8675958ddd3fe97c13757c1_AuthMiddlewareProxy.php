<?php

namespace Rhapsody\Core\Proxy\Generated\Rhapsody_Core_Middleware_AuthMiddleware;

use Rhapsody\Core\Proxy\LazyProxyInterface;

class AuthMiddlewareProxy extends \Rhapsody\Core\Middleware\AuthMiddleware implements \Rhapsody\Core\Proxy\LazyProxyInterface
{
    private ?\Closure $initializer = null;
    private ?object $wrappedObject = null;

    public function __construct(\Closure $initializer)
    {
        $this->initializer = $initializer;
    }

    private function __lazyProxyInitialize(): void
    {
        if ($this->wrappedObject === null && $this->initializer !== null) {
            $initializer = $this->initializer;
            $initializer($this->wrappedObject, $this, 'initialize', [], $this->initializer);
            $this->initializer = null;
        }
    }

    public function getWrappedObject(): ?object
    {
        $this->__lazyProxyInitialize();
        return $this->wrappedObject;
    }

    public function setInitializer(?\Closure $initializer): void
    {
        $this->initializer = $initializer;
    }

    public function getInitializer(): ?\Closure
    {
        return $this->initializer;
    }

    public function isProxyInitialized(): bool
    {
        return $this->wrappedObject !== null;
    }

    public function handle(\Rhapsody\Core\Request $request, ?\Rhapsody\Core\Routing\Route $route = NULL): ?\Rhapsody\Core\Response
    {
        $this->__lazyProxyInitialize();
        return $this->wrappedObject->handle($request, $route);
    }

}