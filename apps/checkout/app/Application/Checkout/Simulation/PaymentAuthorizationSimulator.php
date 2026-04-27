<?php

declare(strict_types=1);

namespace App\Application\Checkout\Simulation;

use App\Domain\Checkout\PaymentMethod;
use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\CheckoutStateRecord;

/**
 * Authorizes fixture payments with deterministic local-only rules.
 */
final readonly class PaymentAuthorizationSimulator
{
    private const CARD_DECLINE_TOKEN = 'simulate-payment-decline';

    /**
     * Authorize the selected payment method before order creation.
     */
    public function authorize(
        TenantContext $tenant,
        CheckoutStateRecord $checkout,
        string $idempotencyKey,
    ): CheckoutSimulationResult {
        $method = PaymentMethod::tryFrom((string) $checkout->payment_method);

        return match ($method) {
            PaymentMethod::Invoice => CheckoutSimulationResult::succeeded(),
            PaymentMethod::Card => $this->authorizeCard($tenant, $checkout, $idempotencyKey),
            null => CheckoutSimulationResult::failed('payment_method_not_selected'),
        };
    }

    /**
     * Decline card payments only when the local deterministic token is present.
     */
    private function authorizeCard(
        TenantContext $tenant,
        CheckoutStateRecord $checkout,
        string $idempotencyKey,
    ): CheckoutSimulationResult {
        $seed = strtolower($tenant->tenantId.'|'.$tenant->shopId.'|'.$checkout->checkout_id.'|'.$idempotencyKey);

        if (str_contains($seed, self::CARD_DECLINE_TOKEN)) {
            return CheckoutSimulationResult::failed('payment_card_declined');
        }

        return CheckoutSimulationResult::succeeded();
    }
}
