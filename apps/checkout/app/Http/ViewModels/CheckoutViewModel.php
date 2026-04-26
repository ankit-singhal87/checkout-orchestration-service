<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;

/**
 * Presentation model for the guest checkout screen.
 */
final readonly class CheckoutViewModel
{
    /**
     * Create a checkout page model.
     *
     * @param  list<array{productName: string, variantLabel: string, quantity: int, priceLabel: string}>  $items
     * @param  list<array{id: string, label: string, priceLabel: string, selected: bool}>  $shippingOptions
     * @param  list<array{id: string, label: string, selected: bool}>  $paymentMethods
     * @param  array{subtotal: string, shipping: string, total: string}  $totals
     * @param  array{name?: string, line1?: string, postal_code?: string, city?: string, country?: string}|null  $shippingAddress
     */
    public function __construct(
        public TenantContext $tenant,
        public string $checkoutId,
        public string $status,
        public array $items,
        public array $shippingOptions,
        public array $paymentMethods,
        public array $totals,
        public ?array $shippingAddress,
        public bool $canConfirm,
        public string $idempotencyKey,
    ) {}
}
