<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;

final readonly class CartViewModel
{
    /**
     * @param list<array{productName: string, variantLabel: string, quantity: int, priceLabel: string}> $items
     */
    public function __construct(
        public TenantContext $tenant,
        public array $items,
    ) {}
}
