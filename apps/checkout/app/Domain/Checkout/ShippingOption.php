<?php

declare(strict_types=1);

namespace App\Domain\Checkout;

/**
 * Simulated shipping options for the Phase 1 checkout flow.
 */
enum ShippingOption: string
{
    case Standard = 'standard';
    case Express = 'express';

    /**
     * Display label for the shipping option.
     */
    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard delivery',
            self::Express => 'Express delivery',
        };
    }

    /**
     * Shipping cost in minor currency units.
     */
    public function amount(): int
    {
        return match ($this) {
            self::Standard => 499,
            self::Express => 999,
        };
    }
}
