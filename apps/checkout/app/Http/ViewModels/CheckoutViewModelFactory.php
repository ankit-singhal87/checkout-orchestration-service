<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Checkout\PaymentMethod;
use App\Domain\Checkout\ShippingOption;
use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\CartItemRecord;
use App\Infrastructure\Persistence\Eloquent\CheckoutStateRecord;
use App\Infrastructure\Persistence\Eloquent\OrderRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;

/**
 * Builds checkout-facing view models from checkout and order records.
 */
final readonly class CheckoutViewModelFactory
{
    /**
     * Build the checkout page model.
     */
    public function checkout(TenantContext $tenant, CheckoutStateRecord $checkout, string $idempotencyKey): CheckoutViewModel
    {
        return new CheckoutViewModel(
            tenant: $tenant,
            checkoutId: (string) $checkout->checkout_id,
            status: $checkout->status->value,
            items: $checkout->cart->items
                ->map(fn (CartItemRecord $item): array => $this->itemView($item))
                ->values()
                ->all(),
            shippingOptions: $this->shippingOptions((string) $checkout->shipping_option),
            paymentMethods: $this->paymentMethods((string) $checkout->payment_method),
            totals: $this->totalLabels($checkout->totals),
            shippingAddress: is_array($checkout->shipping_address) ? $checkout->shipping_address : null,
            canConfirm: $checkout->status->canConfirm(),
            idempotencyKey: $idempotencyKey,
        );
    }

    /**
     * Build the order confirmation page model.
     */
    public function confirmation(TenantContext $tenant, OrderRecord $order): OrderConfirmationViewModel
    {
        return new OrderConfirmationViewModel(
            tenant: $tenant,
            orderRef: (string) $order->order_ref,
            totalLabel: $this->moneyLabel((int) $order->total_amount, (string) $order->total_currency),
            status: $order->status->value,
        );
    }

    /**
     * Convert a persisted cart item into a renderable item shape.
     *
     * @return array{productName: string, variantLabel: string, quantity: int, priceLabel: string}
     */
    private function itemView(CartItemRecord $item): array
    {
        /** @var ProductVariantRecord $variant */
        $variant = $item->variant;

        return [
            'productName' => (string) $variant->product->name,
            'variantLabel' => $this->variantLabel($variant),
            'quantity' => (int) $item->quantity,
            'priceLabel' => $this->moneyLabel((int) $variant->price_amount, (string) $variant->price_currency),
        ];
    }

    /**
     * Build shipping option rows.
     *
     * @return list<array{id: string, label: string, priceLabel: string, selected: bool}>
     */
    private function shippingOptions(string $selected): array
    {
        return array_map(
            fn (ShippingOption $option): array => [
                'id' => $option->value,
                'label' => $option->label(),
                'priceLabel' => $this->moneyLabel($option->amount(), 'EUR'),
                'selected' => $selected === $option->value,
            ],
            ShippingOption::cases(),
        );
    }

    /**
     * Build payment method rows.
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
     * Format total labels for display.
     *
     * @param  array{subtotal?: int, shipping?: int, total?: int, currency?: string}  $totals
     * @return array{subtotal: string, shipping: string, total: string}
     */
    private function totalLabels(array $totals): array
    {
        $currency = (string) ($totals['currency'] ?? 'EUR');

        return [
            'subtotal' => $this->moneyLabel((int) ($totals['subtotal'] ?? 0), $currency),
            'shipping' => $this->moneyLabel((int) ($totals['shipping'] ?? 0), $currency),
            'total' => $this->moneyLabel((int) ($totals['total'] ?? 0), $currency),
        ];
    }

    /**
     * Format a minor-unit price for display.
     */
    private function moneyLabel(int $amount, string $currency): string
    {
        return sprintf('%s %.2f', $currency, $amount / 100);
    }

    /**
     * Format option name/value pairs into a readable variant label.
     */
    private function variantLabel(ProductVariantRecord $variant): string
    {
        $parts = [];

        foreach ($variant->options ?? [] as $name => $value) {
            $parts[] = ucfirst((string) $name).': '.(string) $value;
        }

        return implode(', ', $parts);
    }
}
