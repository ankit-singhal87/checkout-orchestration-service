<?php

declare(strict_types=1);

namespace App\Application\Cart;

use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\CartRecord;

/**
 * Reads tenant-scoped carts for the current shopper session.
 */
final readonly class CartReader
{
    /**
     * Find a cart by tenant and session cart id.
     */
    public function cartForSession(TenantContext $tenant, string $cartId): ?CartRecord
    {
        $cart = CartRecord::query()
            ->where('tenant_record_id', $tenant->recordId)
            ->where('cart_id', $cartId)
            ->with('items.variant.product')
            ->first();

        return $cart instanceof CartRecord ? $cart : null;
    }
}
