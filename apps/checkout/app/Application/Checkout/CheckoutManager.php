<?php

declare(strict_types=1);

namespace App\Application\Checkout;

use App\Application\Checkout\Simulation\InventoryReservationSimulator;
use App\Application\Checkout\Simulation\PaymentAuthorizationSimulator;
use App\Domain\Checkout\CheckoutStatus;
use App\Domain\Checkout\PaymentMethod;
use App\Domain\Checkout\ShippingOption;
use App\Domain\Order\OrderStatus;
use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\CartRecord;
use App\Infrastructure\Persistence\Eloquent\CheckoutStateRecord;
use App\Infrastructure\Persistence\Eloquent\OrderRecord;
use App\Infrastructure\Persistence\Eloquent\OutboxEventRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Owns the guest checkout state transitions for Phase 1.
 */
final readonly class CheckoutManager
{
    public function __construct(
        private CheckoutTotals $totals,
        private InventoryReservationSimulator $inventory,
        private PaymentAuthorizationSimulator $payments,
    ) {}

    /**
     * Start or resume checkout for the current tenant cart.
     */
    public function startForCart(TenantContext $tenant, string $cartId): ?CheckoutStateRecord
    {
        $cart = $this->cartForTenant($tenant, $cartId);

        if (! $cart instanceof CartRecord || $cart->items->isEmpty()) {
            return null;
        }

        /** @var CheckoutStateRecord $checkout */
        $checkout = CheckoutStateRecord::query()->firstOrCreate(
            [
                'tenant_record_id' => $tenant->recordId,
                'cart_record_id' => $cart->id,
            ],
            [
                'checkout_id' => 'chk_'.Str::ulid()->toString(),
                'status' => CheckoutStatus::Created,
                'totals' => $this->totals->forCart($cart, null),
            ],
        );

        return $this->reloadCheckout($checkout);
    }

    /**
     * Apply a shipping address and move checkout to the addressed state.
     *
     * @param  array{name: string, line1: string, postal_code: string, city: string, country: string}  $address
     */
    public function updateAddress(TenantContext $tenant, string $checkoutId, array $address): ?CheckoutStateRecord
    {
        $checkout = $this->checkoutForTenant($tenant, $checkoutId);

        if (! $checkout instanceof CheckoutStateRecord) {
            return null;
        }

        $checkout->shipping_address = $address;
        $checkout->status = CheckoutStatus::Addressed;
        $checkout->save();

        return $this->reloadCheckout($checkout);
    }

    /**
     * Select a shipping option and recalculate totals.
     */
    public function selectShippingOption(TenantContext $tenant, string $checkoutId, ShippingOption $option): ?CheckoutStateRecord
    {
        $checkout = $this->checkoutForTenant($tenant, $checkoutId);

        if (! $checkout instanceof CheckoutStateRecord) {
            return null;
        }

        $checkout->shipping_option = $option->value;
        $checkout->status = CheckoutStatus::ShippingSelected;
        $checkout->totals = $this->totals->forCart($checkout->cart, $option);
        $checkout->save();

        return $this->reloadCheckout($checkout);
    }

    /**
     * Select a simulated payment method.
     */
    public function selectPaymentMethod(TenantContext $tenant, string $checkoutId, PaymentMethod $method): ?CheckoutStateRecord
    {
        $checkout = $this->checkoutForTenant($tenant, $checkoutId);

        if (! $checkout instanceof CheckoutStateRecord || ! is_string($checkout->shipping_option)) {
            return null;
        }

        $checkout->payment_method = $method->value;
        $checkout->status = CheckoutStatus::PaymentSelected;
        $checkout->save();

        return $this->reloadCheckout($checkout);
    }

    /**
     * Update a basket item inside an active checkout state.
     */
    public function updateBasketItemQuantity(TenantContext $tenant, string $checkoutId, string $variantId, int $quantity): ?CheckoutStateRecord
    {
        return DB::transaction(function () use ($tenant, $checkoutId, $variantId, $quantity): ?CheckoutStateRecord {
            /** @var CheckoutStateRecord|null $checkout */
            $checkout = CheckoutStateRecord::query()
                ->where('tenant_record_id', $tenant->recordId)
                ->where('checkout_id', $checkoutId)
                ->with('cart.items.variant.product')
                ->lockForUpdate()
                ->first();

            if (! $checkout instanceof CheckoutStateRecord || $checkout->status === CheckoutStatus::Confirmed) {
                return null;
            }

            $item = $checkout->cart->items->first(function ($item) use ($variantId): bool {
                return $item->variant instanceof ProductVariantRecord
                    && $item->variant->variant_id === $variantId;
            });

            if ($item === null) {
                return null;
            }

            if ($quantity === 0) {
                $item->delete();
            } else {
                $item->quantity = $quantity;
                $item->save();
            }

            $checkout->cart->load('items.variant.product');
            $checkout->shipping_option = null;
            $checkout->payment_method = null;
            $checkout->status = is_array($checkout->shipping_address) ? CheckoutStatus::Addressed : CheckoutStatus::Created;
            $checkout->totals = $this->totals->forCart($checkout->cart, null);
            $checkout->save();

            return $this->reloadCheckout($checkout);
        });
    }

    /**
     * Confirm checkout idempotently and enqueue post-order side effects.
     */
    public function confirm(TenantContext $tenant, string $checkoutId, string $idempotencyKey): ?OrderRecord
    {
        $result = $this->confirmOrder($tenant, $checkoutId, $idempotencyKey);

        return $result->order;
    }

    /**
     * Confirm checkout and preserve conflict details for public API boundaries.
     */
    public function confirmOrder(TenantContext $tenant, string $checkoutId, string $idempotencyKey): ConfirmCheckoutResult
    {
        return DB::transaction(function () use ($tenant, $checkoutId, $idempotencyKey): ConfirmCheckoutResult {
            /** @var CheckoutStateRecord|null $checkout */
            $checkout = CheckoutStateRecord::query()
                ->where('tenant_record_id', $tenant->recordId)
                ->where('checkout_id', $checkoutId)
                ->with(['cart.items.variant.product', 'order'])
                ->lockForUpdate()
                ->first();

            if (! $checkout instanceof CheckoutStateRecord) {
                return ConfirmCheckoutResult::notFound();
            }

            $existingOrder = OrderRecord::query()
                ->where('tenant_record_id', $tenant->recordId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingOrder instanceof OrderRecord) {
                if ((int) $existingOrder->checkout_state_record_id !== (int) $checkout->id) {
                    return ConfirmCheckoutResult::idempotencyConflict();
                }

                return ConfirmCheckoutResult::replayed($existingOrder);
            }

            if ($checkout->status === CheckoutStatus::Confirmed) {
                return ConfirmCheckoutResult::idempotencyConflict();
            }

            if (! $checkout->status->canConfirm()) {
                return ConfirmCheckoutResult::notReady();
            }

            $payment = $this->payments->authorize($tenant, $checkout, $idempotencyKey);
            if (! $payment->successful) {
                $checkout->status = CheckoutStatus::Failed;
                $checkout->save();

                return ConfirmCheckoutResult::simulatorFailed($payment->reason);
            }

            $inventory = $this->inventory->reserve($tenant, $checkout);
            if (! $inventory->successful) {
                $checkout->status = CheckoutStatus::Failed;
                $checkout->save();

                return ConfirmCheckoutResult::simulatorFailed($inventory->reason);
            }

            $orderRef = 'ord_'.Str::ulid()->toString();
            $totals = $checkout->totals;

            /** @var OrderRecord $order */
            $order = OrderRecord::query()->create([
                'tenant_record_id' => $tenant->recordId,
                'checkout_state_record_id' => $checkout->id,
                'order_ref' => $orderRef,
                'idempotency_key' => $idempotencyKey,
                'status' => OrderStatus::Confirmed,
                'cart_snapshot' => $this->cartSnapshot($checkout->cart),
                'total_amount' => (int) $totals['total'],
                'total_currency' => (string) $totals['currency'],
            ]);

            $checkout->status = CheckoutStatus::Confirmed;
            $checkout->save();

            OutboxEventRecord::query()->create([
                'tenant_record_id' => $tenant->recordId,
                'event_id' => 'evt_'.Str::ulid()->toString(),
                'event_type' => 'order.confirmed',
                'aggregate_type' => 'order',
                'aggregate_id' => $order->order_ref,
                'payload' => $this->orderConfirmedPayload($tenant, $checkout, $order, $idempotencyKey),
            ]);

            return ConfirmCheckoutResult::confirmed($order);
        });
    }

    /**
     * Find a checkout state by tenant and public checkout id.
     */
    public function checkoutForTenant(TenantContext $tenant, string $checkoutId): ?CheckoutStateRecord
    {
        $checkout = CheckoutStateRecord::query()
            ->where('tenant_record_id', $tenant->recordId)
            ->where('checkout_id', $checkoutId)
            ->with('cart.items.variant.product')
            ->first();

        return $checkout instanceof CheckoutStateRecord ? $checkout : null;
    }

    /**
     * Find an order by tenant and public order reference.
     */
    public function orderForTenant(TenantContext $tenant, string $orderRef): ?OrderRecord
    {
        $order = OrderRecord::query()
            ->where('tenant_record_id', $tenant->recordId)
            ->where('order_ref', $orderRef)
            ->first();

        return $order instanceof OrderRecord ? $order : null;
    }

    /**
     * Load the cart that belongs to the resolved tenant.
     */
    private function cartForTenant(TenantContext $tenant, string $cartId): ?CartRecord
    {
        $cart = CartRecord::query()
            ->where('tenant_record_id', $tenant->recordId)
            ->where('cart_id', $cartId)
            ->with('items.variant.product')
            ->first();

        return $cart instanceof CartRecord ? $cart : null;
    }

    /**
     * Reload relations after a checkout state mutation.
     */
    private function reloadCheckout(CheckoutStateRecord $checkout): CheckoutStateRecord
    {
        return $checkout->fresh(['cart.items.variant.product', 'order']) ?? $checkout;
    }

    /**
     * Capture order line data at confirmation time.
     *
     * @return list<array{productName: string, variantId: string, quantity: int, unitPrice: int}>
     */
    private function cartSnapshot(CartRecord $cart): array
    {
        $items = [];

        foreach ($cart->items as $item) {
            $variant = $item->variant;

            $items[] = [
                'productName' => (string) $variant->product->name,
                'variantId' => (string) $variant->variant_id,
                'quantity' => (int) $item->quantity,
                'unitPrice' => (int) $variant->price_amount,
            ];
        }

        return $items;
    }

    /**
     * Build the schema-versioned order.confirmed payload and envelope context.
     *
     * @return array<string, mixed>
     */
    private function orderConfirmedPayload(
        TenantContext $tenant,
        CheckoutStateRecord $checkout,
        OrderRecord $order,
        string $idempotencyKey,
    ): array {
        return [
            'orderRef' => $order->order_ref,
            'tenant' => $tenant->tenantId,
            'shop' => $tenant->shopId,
            'total' => $order->total_amount,
            'currency' => $order->total_currency,
            'correlationId' => $checkout->checkout_id,
            'causationId' => 'checkout.confirmation:'.$checkout->checkout_id,
            'idempotencyKey' => $tenant->tenantId.':order.confirmed:'.$idempotencyKey,
        ];
    }
}
