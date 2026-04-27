<?php

declare(strict_types=1);

namespace App\Application\Checkout;

use App\Infrastructure\Persistence\Eloquent\OrderRecord;

/**
 * Result object for checkout confirmation without losing conflict semantics.
 */
final readonly class ConfirmCheckoutResult
{
    private function __construct(
        public ConfirmCheckoutStatus $status,
        public ?OrderRecord $order = null,
        public ?string $failureReason = null,
    ) {}

    /**
     * Build a successful newly confirmed result.
     */
    public static function confirmed(OrderRecord $order): self
    {
        return new self(ConfirmCheckoutStatus::Confirmed, $order);
    }

    /**
     * Build a successful idempotent replay result.
     */
    public static function replayed(OrderRecord $order): self
    {
        return new self(ConfirmCheckoutStatus::Replayed, $order);
    }

    /**
     * Build a failed result with no visible checkout state.
     */
    public static function notFound(): self
    {
        return new self(ConfirmCheckoutStatus::NotFound);
    }

    /**
     * Build a failed result for an incomplete checkout state.
     */
    public static function notReady(): self
    {
        return new self(ConfirmCheckoutStatus::NotReady);
    }

    /**
     * Build a failed result for conflicting idempotency or confirmed state.
     */
    public static function idempotencyConflict(): self
    {
        return new self(ConfirmCheckoutStatus::IdempotencyConflict);
    }

    /**
     * Build a failed result for deterministic simulator business outcomes.
     */
    public static function simulatorFailed(string $reason): self
    {
        return new self(ConfirmCheckoutStatus::NotReady, failureReason: $reason);
    }
}
