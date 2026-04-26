<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Catalog\StockState;
use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;

/**
 * Builds product-facing view models from tenant-scoped product records.
 */
final readonly class ProductViewModelFactory
{
    /**
     * Build the product card shown in listings.
     */
    public function card(ProductRecord $product): ProductCardViewModel
    {
        $variant = $product->variants->first();

        return new ProductCardViewModel(
            slug: (string) $product->slug,
            name: (string) $product->name,
            description: (string) $product->description,
            imageAlt: (string) data_get($product->image, 'alt', $product->name),
            imageKey: (string) data_get($product->image, 'placeholder', 'product-generic'),
            badges: array_values($product->badges ?? []),
            priceLabel: $variant instanceof ProductVariantRecord ? $this->priceLabel($variant) : 'Price unavailable',
        );
    }

    /**
     * Build the product detail page model for a resolved tenant.
     */
    public function detail(TenantContext $tenant, ProductRecord $product): ProductDetailViewModel
    {
        return new ProductDetailViewModel(
            tenant: $tenant,
            slug: (string) $product->slug,
            name: (string) $product->name,
            description: (string) $product->description,
            imageAlt: (string) data_get($product->image, 'alt', $product->name),
            imageKey: (string) data_get($product->image, 'placeholder', 'product-generic'),
            badges: array_values($product->badges ?? []),
            variants: $product->variants
                ->map(fn (ProductVariantRecord $variant): array => [
                    'variantId' => (string) $variant->variant_id,
                    'label' => $this->variantLabel($variant),
                    'priceLabel' => $this->priceLabel($variant),
                    'stockState' => StockState::fromStorage($variant->stock_state)->value,
                    'available' => (int) $variant->stock_available,
                ])
                ->values()
                ->all(),
        );
    }

    /**
     * Format a minor-unit price for display.
     */
    private function priceLabel(ProductVariantRecord $variant): string
    {
        return sprintf('%s %.2f', $variant->price_currency, $variant->price_amount / 100);
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
