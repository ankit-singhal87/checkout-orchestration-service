<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Checkout\PaymentMethod;
use App\Domain\Checkout\ShippingOption;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Returns cacheable public checkout configuration for a resolved tenant.
 */
final class CheckoutConfigController extends Controller
{
    /**
     * Show tenant checkout configuration.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);

        return response()->json([
            'tenant' => [
                'id' => $tenant->tenantId,
                'shop' => $tenant->shopId,
                'displayName' => $tenant->displayName,
                'currency' => $tenant->currency,
                'locale' => $tenant->locale,
                'primaryColor' => $tenant->primaryColor,
                'trustBadges' => $tenant->trustBadges,
            ],
            'shippingOptions' => array_map(
                fn (ShippingOption $option): array => [
                    'id' => $option->value,
                    'label' => $option->label(),
                    'amount' => $option->amount(),
                    'currency' => $tenant->currency,
                ],
                ShippingOption::cases(),
            ),
            'paymentMethods' => array_map(
                fn (PaymentMethod $method): array => [
                    'id' => $method->value,
                    'label' => $method->label(),
                ],
                PaymentMethod::cases(),
            ),
            'features' => [
                'guestCheckout' => true,
                'postCheckoutAccountPrompt' => true,
                'simulatedPayments' => true,
            ],
        ])->header('Cache-Control', 'public, max-age=60');
    }
}
