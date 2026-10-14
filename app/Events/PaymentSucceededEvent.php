<?php
namespace App\Events;

/**
 * Dispatched when a payment succeeds (e.g., charge.succeeded, payment_intent.succeeded).
 */
class PaymentSucceededEvent
{
    public function __construct(
        public readonly string $transactionId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly array $metadata = []
    ) {}
}
