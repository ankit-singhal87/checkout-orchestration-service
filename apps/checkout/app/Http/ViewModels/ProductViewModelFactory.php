<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;

final class ProductViewModelFactory
{
    public function card(ProductRecord $product): ProductCardViewModel
    {
        $variant = $product->variants->first();

        return new ProductCardViewModel(
            slug: (string) $product->slug,
            name: (string) $product->name,
            description: (string) $product->description,
            imageAlt: (string) data_get($product->image, 'alt', $product->name),
            badges: array_values($product->badges ?? []),
            priceLabel: $variant instanceof ProductVariantRecord ? $this->priceLabel($variant) : 'Price unavailable',
        );
    }

    public function detail(TenantContext $tenant, ProductRecord $product): ProductDetailViewModel
    {
        return new ProductDetailViewModel(
            tenant: $tenant,
            slug: (string) $product->slug,
            name: (string) $product->name,
            description: (string) $product->description,
            imageAlt: (string) data_get($product->image, 'alt', $product->name),
            badges: array_values($product->badges ?? []),
            variants: $product->variants
                ->map(fn (ProductVariantRecord $variant): array => [
                    'variantId' => (string) $variant->variant_id,
                    'label' => collect($variant->options ?? [])
                        ->map(fn (string $value, string $name): string => ucfirst($name).': '.$value)
                        ->implode(', '),
                    'priceLabel' => $this->priceLabel($variant),
                    'stockState' => (string) $variant->stock_state,
                    'available' => (int) $variant->stock_available,
                ])
                ->values()
                ->all(),
        );
    }

    private function priceLabel(ProductVariantRecord $variant): string
    {
        return sprintf('%s %.2f', $variant->price_currency, $variant->price_amount / 100);
    }
}
