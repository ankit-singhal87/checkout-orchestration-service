<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\CartItemRecord;
use App\Infrastructure\Persistence\Eloquent\CartRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;

/**
 * Builds cart-facing view models from tenant-scoped cart records.
 */
final readonly class CartViewModelFactory
{
    /**
     * Build a cart page model, including the empty-cart state.
     */
    public function make(TenantContext $tenant, ?CartRecord $cart): CartViewModel
    {
        if ($cart === null) {
            return new CartViewModel($tenant, []);
        }

        return new CartViewModel(
            tenant: $tenant,
            items: $cart->items
                ->map(fn (CartItemRecord $item): array => $this->itemView($item))
                ->values()
                ->all(),
        );
    }

    /**
     * Convert a persisted cart item into a renderable item shape.
     *
     * @return array{productName: string, variantLabel: string, quantity: int, priceLabel: string}
     */
    private function itemView(CartItemRecord $item): array
    {
        /** @var ProductVariantRecord $variant */
        $variant = $item->variant;

        return [
            'productName' => (string) $variant->product->name,
            'variantLabel' => $this->variantLabel($variant),
            'quantity' => (int) $item->quantity,
            'priceLabel' => sprintf('%s %.2f', $variant->price_currency, $variant->price_amount / 100),
        ];
    }

    /**
     * Format option name/value pairs into a readable variant label.
     */
    private function variantLabel(ProductVariantRecord $variant): string
    {
        $parts = [];

        foreach ($variant->options ?? [] as $name => $value) {
            $parts[] = ucfirst((string) $name).': '.(string) $value;
        }

        return implode(', ', $parts);
    }
}
