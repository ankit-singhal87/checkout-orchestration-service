<?php

declare(strict_types=1);

namespace App\Domain\Checkout;

/**
 * Simulated payment methods for the Phase 1 checkout flow.
 */
enum PaymentMethod: string
{
    case Invoice = 'invoice';
    case Card = 'card';

    /**
     * Display label for the payment method.
     */
    public function label(): string
    {
        return match ($this) {
            self::Invoice => 'Invoice',
            self::Card => 'Card simulation',
        };
    }
}
