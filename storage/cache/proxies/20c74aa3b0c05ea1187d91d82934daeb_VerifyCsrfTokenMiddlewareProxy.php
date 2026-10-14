<?php

namespace Rhapsody\Core\Proxy\Generated\Rhapsody_Core_Middleware_VerifyCsrfTokenMiddleware;

use Rhapsody\Core\Proxy\LazyProxyInterface;

class VerifyCsrfTokenMiddlewareProxy extends \Rhapsody\Core\Middleware\VerifyCsrfTokenMiddleware implements \Rhapsody\Core\Proxy\LazyProxyInterface
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

    protected function isMutatingRequest(\Rhapsody\Core\Request $request): bool
    {
        $this->__lazyProxyInitialize();
        return $this->wrappedObject->isMutatingRequest($request);
    }

    protected function inExceptArray(\Rhapsody\Core\Request $request): bool
    {
        $this->__lazyProxyInitialize();
        return $this->wrappedObject->inExceptArray($request);
    }

}