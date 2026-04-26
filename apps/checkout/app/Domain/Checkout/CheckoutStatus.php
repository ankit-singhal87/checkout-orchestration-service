<?php

declare(strict_types=1);

namespace App\Domain\Checkout;

/**
 * Checkout state machine statuses used by the web checkout slice.
 */
enum CheckoutStatus: string
{
    case Created = 'created';
    case Addressed = 'addressed';
    case ShippingSelected = 'shipping_selected';
    case PaymentSelected = 'payment_selected';
    case Confirmed = 'confirmed';

    /**
     * Determine whether order confirmation can be attempted.
     */
    public function canConfirm(): bool
    {
        return $this === self::PaymentSelected;
    }
}
