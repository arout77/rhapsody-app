<?php
namespace App\Events;

/**
 * Dispatched when a payment succeeds (e.g., charge.succeeded, payment_intent.succeeded).
 */
class PaymentSucceededEvent
{
    /**
     * @param array<string, mixed> $metadata Gateway metadata (e.g. Stripe metadata)
     */
    public function __construct(
        public readonly string $transactionId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly array $metadata = []
    ) {}
}
