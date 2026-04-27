<?php

declare(strict_types=1);

namespace App\Application\Checkout\Simulation;

use App\Domain\Catalog\StockState;
use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\CheckoutStateRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;

/**
 * Reserves fixture-backed inventory deterministically inside checkout confirmation.
 */
final readonly class InventoryReservationSimulator
{
    /**
     * Reserve every cart line for the tenant before an order can be committed.
     */
    public function reserve(TenantContext $tenant, CheckoutStateRecord $checkout): CheckoutSimulationResult
    {
        foreach ($checkout->cart->items as $item) {
            /** @var ProductVariantRecord|null $variant */
            $variant = ProductVariantRecord::query()
                ->where('tenant_record_id', $tenant->recordId)
                ->whereKey($item->product_variant_record_id)
                ->lockForUpdate()
                ->first();

            if (! $variant instanceof ProductVariantRecord) {
                return CheckoutSimulationResult::failed('inventory_variant_not_visible');
            }

            $requested = (int) $item->quantity;
            $available = (int) $variant->stock_available;
            $stockState = StockState::fromStorage($variant->stock_state);

            if ($requested < 1 || ! $stockState->canAddToCart($available) || $available < $requested) {
                return CheckoutSimulationResult::failed('inventory_insufficient_stock');
            }

            $remaining = $available - $requested;
            $variant->stock_available = $remaining;
            $variant->stock_state = $remaining === 0 ? StockState::OutOfStock : $stockState;
            $variant->save();
        }

        return CheckoutSimulationResult::succeeded();
    }
}
