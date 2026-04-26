<?php

declare(strict_types=1);

namespace App\Application\Cart;

/**
 * Closed set of add-to-cart outcomes.
 */
enum CartAddStatus: string
{
    case Added = 'added';
    case TenantAccessDenied = 'tenant_access_denied';
    case OutOfStock = 'out_of_stock';

    /**
     * Determine whether this outcome represents a successful add.
     */
    public function added(): bool
    {
        return $this === self::Added;
    }

    /**
     * Return the RFC 9457 problem type path for this outcome.
     */
    public function problemType(): ?string
    {
        return match ($this) {
            self::Added => null,
            self::TenantAccessDenied => '/problems/tenant-access-denied',
            self::OutOfStock => '/problems/validation-failed',
        };
    }

    /**
     * Return the RFC 9457 problem title for this outcome.
     */
    public function problemTitle(): ?string
    {
        return match ($this) {
            self::Added => null,
            self::TenantAccessDenied => 'Tenant access denied',
            self::OutOfStock => 'Cart item unavailable',
        };
    }

    /**
     * Return the HTTP status for this outcome.
     */
    public function problemStatus(): ?int
    {
        return match ($this) {
            self::Added => null,
            self::TenantAccessDenied => 403,
            self::OutOfStock => 422,
        };
    }

    /**
     * Return the problem detail for this outcome.
     */
    public function problemDetail(): ?string
    {
        return match ($this) {
            self::Added => null,
            self::TenantAccessDenied => 'The requested cart item is not available for this shop.',
            self::OutOfStock => 'The selected variant is not available.',
        };
    }
}
