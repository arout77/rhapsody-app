<?php
namespace App\Events;

/**
 * Dispatched when a payment fails.
 */
class PaymentFailedEvent
{
    /**
     * @param array<string, mixed> $context Gateway payload or request details for logging
     */
    public function __construct(
        public readonly string $message,
        public readonly array $context = []
    ) {}
}
