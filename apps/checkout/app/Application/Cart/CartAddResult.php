<?php

declare(strict_types=1);

namespace App\Application\Cart;

/**
 * Immutable result returned by the add-to-cart use case.
 */
final readonly class CartAddResult
{
    /**
     * Create a result for a handled cart add outcome.
     */
    private function __construct(public CartAddStatus $status) {}

    /**
     * Create a successful add-to-cart result.
     */
    #[\NoDiscard]
    public static function added(): self
    {
        return new self(CartAddStatus::Added);
    }

    /**
     * Create a result for a variant outside the resolved tenant.
     */
    #[\NoDiscard]
    public static function tenantAccessDenied(): self
    {
        return new self(CartAddStatus::TenantAccessDenied);
    }

    /**
     * Create a result for a variant that cannot be added because stock is unavailable.
     */
    #[\NoDiscard]
    public static function outOfStock(): self
    {
        return new self(CartAddStatus::OutOfStock);
    }

    /**
     * Determine whether the item was added.
     */
    public function isAdded(): bool
    {
        return $this->status->added();
    }

    /**
     * Return the RFC 9457 problem type path for failed outcomes.
     */
    public function problemType(): ?string
    {
        return $this->status->problemType();
    }

    /**
     * Return the RFC 9457 problem title for failed outcomes.
     */
    public function problemTitle(): ?string
    {
        return $this->status->problemTitle();
    }

    /**
     * Return the HTTP status for failed outcomes.
     */
    public function problemStatus(): ?int
    {
        return $this->status->problemStatus();
    }

    /**
     * Return the human-readable problem detail for failed outcomes.
     */
    public function problemDetail(): ?string
    {
        return $this->status->problemDetail();
    }
}
