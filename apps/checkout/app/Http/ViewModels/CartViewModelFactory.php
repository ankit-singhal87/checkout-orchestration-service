<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\CartRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;

final class CartViewModelFactory
{
    public function make(TenantContext $tenant, ?CartRecord $cart): CartViewModel
    {
        if ($cart === null) {
            return new CartViewModel($tenant, []);
        }

        return new CartViewModel(
            tenant: $tenant,
            items: $cart->items
                ->map(function ($item): array {
                    /** @var ProductVariantRecord $variant */
                    $variant = $item->variant;

                    return [
                        'productName' => (string) $variant->product->name,
                        'variantLabel' => collect($variant->options ?? [])
                            ->map(fn (string $value, string $name): string => ucfirst($name).': '.$value)
                            ->implode(', '),
                        'quantity' => (int) $item->quantity,
                        'priceLabel' => sprintf('%s %.2f', $variant->price_currency, $variant->price_amount / 100),
                    ];
                })
                ->values()
                ->all(),
        );
    }
}
