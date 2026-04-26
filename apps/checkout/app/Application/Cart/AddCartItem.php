<?php

declare(strict_types=1);

namespace App\Application\Cart;

use App\Domain\Catalog\StockState;
use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\CartItemRecord;
use App\Infrastructure\Persistence\Eloquent\CartRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;
use Illuminate\Support\Facades\DB;

/**
 * Adds tenant-owned variants to a shopper cart.
 */
final readonly class AddCartItem
{
    /**
     * Add a variant to a cart when it belongs to the tenant and has stock.
     */
    #[\NoDiscard]
    public function add(TenantContext $tenant, string $cartId, string $variantId, int $quantity = 1): CartAddResult
    {
        $variant = ProductVariantRecord::query()
            ->where('variant_id', $variantId)
            ->with('product')
            ->first();

        if (! $variant instanceof ProductVariantRecord || (int) $variant->tenant_record_id !== $tenant->recordId) {
            return CartAddResult::tenantAccessDenied();
        }

        $stockState = StockState::fromStorage($variant->stock_state);

        if (! $stockState->canAddToCart((int) $variant->stock_available)) {
            return CartAddResult::outOfStock();
        }

        return DB::transaction(function () use ($tenant, $cartId, $variant, $quantity): CartAddResult {
            /** @var CartRecord $cart */
            $cart = CartRecord::query()->firstOrCreate(
                [
                    'tenant_record_id' => $tenant->recordId,
                    'cart_id' => $cartId,
                ],
            );

            /** @var CartItemRecord $item */
            $item = CartItemRecord::query()->firstOrNew([
                'cart_record_id' => $cart->id,
                'product_variant_record_id' => $variant->id,
            ]);

            $item->quantity = ((int) $item->quantity) + $quantity;
            $item->save();

            return CartAddResult::added();
        });
    }
}
