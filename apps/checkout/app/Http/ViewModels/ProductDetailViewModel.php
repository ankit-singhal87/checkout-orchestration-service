<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;

final readonly class ProductDetailViewModel
{
    /**
     * @param list<array{variantId: string, label: string, priceLabel: string, stockState: string, available: int}> $variants
     * @param list<string> $badges
     */
    public function __construct(
        public TenantContext $tenant,
        public string $slug,
        public string $name,
        public string $description,
        public string $imageAlt,
        public array $badges,
        public array $variants,
    ) {}
}
