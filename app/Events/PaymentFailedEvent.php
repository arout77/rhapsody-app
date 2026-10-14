<?php
namespace App\Events;

/**
 * Dispatched when a payment fails.
 */
class PaymentFailedEvent
{
    public function __construct(
        public readonly string $message,
        public readonly array $context = []
    ) {}
}
