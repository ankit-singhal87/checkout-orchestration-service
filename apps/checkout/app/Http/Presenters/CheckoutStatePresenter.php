<?php

declare(strict_types=1);

namespace App\Http\Presenters;

use App\Domain\Checkout\PaymentMethod;
use App\Domain\Checkout\ShippingOption;
use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\CartItemRecord;
use App\Infrastructure\Persistence\Eloquent\CheckoutStateRecord;
use App\Infrastructure\Persistence\Eloquent\OrderRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;

/**
 * Converts checkout persistence records into public API response shapes.
 */
final readonly class CheckoutStatePresenter
{
    /**
     * Present a checkout state response.
     *
     * @return array<string, mixed>
     */
    public function state(TenantContext $tenant, CheckoutStateRecord $checkout): array
    {
        return [
            'tenant' => $tenant->tenantId,
            'shop' => $tenant->shopId,
            'checkoutId' => (string) $checkout->checkout_id,
            'status' => $checkout->status->value,
            'basket' => [
                'items' => $checkout->cart->items
                    ->map(fn (CartItemRecord $item): array => $this->item($item))
                    ->values()
                    ->all(),
            ],
            'shippingAddress' => $checkout->shipping_address,
            'shippingOptions' => $this->shippingOptions((string) $checkout->shipping_option, $tenant->currency),
            'paymentMethods' => $this->paymentMethods((string) $checkout->payment_method),
            'totals' => $checkout->totals,
            'nextActions' => $this->nextActions($checkout),
        ];
    }

    /**
     * Present an order-confirmation response.
     *
     * @return array<string, mixed>
     */
    public function order(TenantContext $tenant, OrderRecord $order): array
    {
        return [
            'tenant' => $tenant->tenantId,
            'shop' => $tenant->shopId,
            'orderRef' => (string) $order->order_ref,
            'status' => $order->status->value,
            'total' => [
                'amount' => (int) $order->total_amount,
                'currency' => (string) $order->total_currency,
            ],
        ];
    }

    /**
     * Present a cart item.
     *
     * @return array{productName: string, variantId: string, options: array<string, mixed>, quantity: int, unitPrice: array{amount: int, currency: string}}
     */
    private function item(CartItemRecord $item): array
    {
        /** @var ProductVariantRecord $variant */
        $variant = $item->variant;

        return [
            'productName' => (string) $variant->product->name,
            'variantId' => (string) $variant->variant_id,
            'options' => $variant->options ?? [],
            'quantity' => (int) $item->quantity,
            'unitPrice' => [
                'amount' => (int) $variant->price_amount,
                'currency' => (string) $variant->price_currency,
            ],
        ];
    }

    /**
     * Build available shipping option payloads.
     *
     * @return list<array{id: string, label: string, amount: int, currency: string, selected: bool}>
     */
    private function shippingOptions(string $selected, string $currency): array
    {
        return array_map(
            fn (ShippingOption $option): array => [
                'id' => $option->value,
                'label' => $option->label(),
                'amount' => $option->amount(),
                'currency' => $currency,
                'selected' => $selected === $option->value,
            ],
            ShippingOption::cases(),
        );
    }

    /**
     * Build available payment method payloads.
     *
     * @return list<array{id: string, label: string, selected: bool}>
     */
    private function paymentMethods(string $selected): array
    {
        return array_map(
            fn (PaymentMethod $method): array => [
                'id' => $method->value,
                'label' => $method->label(),
                'selected' => $selected === $method->value,
            ],
            PaymentMethod::cases(),
        );
    }

    /**
     * Return the next allowed API actions for the state.
     *
     * @return list<string>
     */
    private function nextActions(CheckoutStateRecord $checkout): array
    {
        if ($checkout->status->canConfirm()) {
            return ['confirm_order'];
        }

        if ($checkout->payment_method === null) {
            return ['update_address', 'select_shipping_option', 'select_payment_method'];
        }

        return [];
    }
}
