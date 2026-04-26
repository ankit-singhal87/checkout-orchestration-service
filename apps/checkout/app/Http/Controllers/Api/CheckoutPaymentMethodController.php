<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Checkout\CheckoutManager;
use App\Domain\Checkout\PaymentMethod;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\Presenters\CheckoutStatePresenter;
use App\Http\Responses\ProblemDetailsResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

/**
 * Handles checkout payment selection for the public API.
 */
final class CheckoutPaymentMethodController extends Controller
{
    /**
     * Create the payment method controller.
     */
    public function __construct(
        private readonly CheckoutManager $checkout,
        private readonly CheckoutStatePresenter $presenter,
        private readonly ProblemDetailsResponse $problems,
    ) {}

    /**
     * Select a simulated payment method.
     */
    public function put(Request $request): JsonResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $validated = $request->validate([
            'checkoutId' => ['required', 'string'],
            'paymentMethod' => ['required', new Enum(PaymentMethod::class)],
        ]);

        $checkout = $this->checkout->selectPaymentMethod(
            tenant: $tenant,
            checkoutId: (string) $validated['checkoutId'],
            method: PaymentMethod::from((string) $validated['paymentMethod']),
        );

        if ($checkout === null) {
            return $this->problems->make(
                request: $request,
                tenant: $tenant,
                type: 'checkout-state-conflict',
                title: 'Checkout state conflict',
                status: 409,
                detail: 'Select a shipping option before selecting a payment method.',
            );
        }

        return response()->json($this->presenter->state($tenant, $checkout));
    }
}
