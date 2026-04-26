<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Tenant\TenantContext;

/**
 * Presentation model for the order confirmation screen.
 */
final readonly class OrderConfirmationViewModel
{
    /**
     * Create an order confirmation page model.
     */
    public function __construct(
        public TenantContext $tenant,
        public string $orderRef,
        public string $totalLabel,
        public string $status,
    ) {}
}
