<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;

final readonly class ShopViewModel
{
    /**
     * @param list<ProductCardViewModel> $products
     */
    public function __construct(
        public TenantContext $tenant,
        public array $products,
    ) {}
}
