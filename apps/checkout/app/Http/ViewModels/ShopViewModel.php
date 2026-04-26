<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;

/**
 * Presentation model for the shop landing page.
 */
final readonly class ShopViewModel
{
    /**
     * Create a shop page model.
     *
     * @param  list<ProductCardViewModel>  $products
     */
    public function __construct(
        public TenantContext $tenant,
        public array $products,
    ) {}
}
