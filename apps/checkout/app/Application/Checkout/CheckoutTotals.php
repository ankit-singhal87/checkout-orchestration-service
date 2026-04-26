<?php

declare(strict_types=1);

namespace App\Application\Checkout;

use App\Domain\Checkout\ShippingOption;
use App\Infrastructure\Persistence\Eloquent\CartItemRecord;
use App\Infrastructure\Persistence\Eloquent\CartRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;

/**
 * Calculates deterministic totals for the local checkout demo.
 */
final readonly class CheckoutTotals
{
    /**
     * Calculate subtotal, shipping, and grand total for a cart.
     *
     * @return array{subtotal: int, shipping: int, total: int, currency: string}
     */
    public function forCart(CartRecord $cart, ?ShippingOption $shippingOption): array
    {
        $subtotal = 0;
        $currency = 'EUR';

        foreach ($cart->items as $item) {
            /** @var CartItemRecord $item */
            /** @var ProductVariantRecord $variant */
            $variant = $item->variant;
            $subtotal += ((int) $variant->price_amount) * ((int) $item->quantity);
            $currency = (string) $variant->price_currency;
        }

        $shipping = $shippingOption?->amount() ?? 0;

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'currency' => $currency,
        ];
    }
}
