<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;

/**
 * Presentation model for a tenant-scoped product detail page.
 */
final readonly class ProductDetailViewModel
{
    /**
     * Create a product detail model.
     *
     * @param  list<array{variantId: string, label: string, priceLabel: string, stockState: string, available: int}>  $variants
     * @param  list<string>  $badges
     */
    public function __construct(
        public TenantContext $tenant,
        public string $slug,
        public string $name,
        public string $description,
        public string $imageAlt,
        public string $imageKey,
        public array $badges,
        public array $variants,
    ) {}
}
