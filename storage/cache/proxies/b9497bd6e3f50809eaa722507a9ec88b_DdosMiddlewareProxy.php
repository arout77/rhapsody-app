<?php

namespace Rhapsody\Core\Proxy\Generated\Rhapsody_Core_Middleware_DdosMiddleware;

use Rhapsody\Core\Proxy\LazyProxyInterface;

class DdosMiddlewareProxy extends \Rhapsody\Core\Middleware\DdosMiddleware implements \Rhapsody\Core\Proxy\LazyProxyInterface
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

    protected function getClientIp(\Rhapsody\Core\Request $request): string
    {
        $this->__lazyProxyInitialize();
        return $this->wrappedObject->getClientIp($request);
    }

    protected function rateLimitExceededResponse(int $retryAfter): \Rhapsody\Core\Response
    {
        $this->__lazyProxyInitialize();
        return $this->wrappedObject->rateLimitExceededResponse($retryAfter);
    }

    protected function blockedResponse(string $ip): \Rhapsody\Core\Response
    {
        $this->__lazyProxyInitialize();
        return $this->wrappedObject->blockedResponse($ip);
    }

}