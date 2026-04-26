<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;

/**
 * Presentation model for the cart page.
 */
final readonly class CartViewModel
{
    /**
     * Create a cart view model.
     *
     * @param  list<array{productName: string, variantLabel: string, quantity: int, priceLabel: string}>  $items
     */
    public function __construct(
        public TenantContext $tenant,
        public array $items,
    ) {}
}
