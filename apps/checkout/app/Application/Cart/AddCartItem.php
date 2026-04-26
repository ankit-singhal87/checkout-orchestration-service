<?php

declare(strict_types=1);

namespace App\Application\Cart;

use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\CartItemRecord;
use App\Infrastructure\Persistence\Eloquent\CartRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;
use Illuminate\Support\Facades\DB;

final class AddCartItem
{
    public function add(TenantContext $tenant, string $cartId, string $variantId, int $quantity = 1): CartAddResult
    {
        return DB::transaction(function () use ($tenant, $cartId, $variantId, $quantity): CartAddResult {
            $variant = ProductVariantRecord::query()
                ->where('variant_id', $variantId)
                ->with('product')
                ->first();

            if (! $variant instanceof ProductVariantRecord || (int) $variant->tenant_record_id !== $tenant->recordId) {
                return CartAddResult::tenantAccessDenied();
            }

            if ((int) $variant->stock_available < 1) {
                return CartAddResult::outOfStock();
            }

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
