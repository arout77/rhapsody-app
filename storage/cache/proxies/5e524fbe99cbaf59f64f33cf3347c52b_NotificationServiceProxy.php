<?php

namespace Rhapsody\Core\Proxy\Generated\App_Services_NotificationService;

use Rhapsody\Core\Proxy\LazyProxyInterface;

class NotificationServiceProxy extends \App\Services\NotificationService implements \Rhapsody\Core\Proxy\LazyProxyInterface
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

    public function injectBanner(\Rhapsody\Core\Response $response): \Rhapsody\Core\Response
    {
        $this->__lazyProxyInitialize();
        return $this->wrappedObject->injectBanner($response);
    }

    protected function checkForUpdatesThrottled(): void
    {
        $this->__lazyProxyInitialize();
        $this->wrappedObject->checkForUpdatesThrottled();
    }

}