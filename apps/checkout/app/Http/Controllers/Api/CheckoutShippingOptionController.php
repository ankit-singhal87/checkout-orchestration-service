<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Checkout\CheckoutManager;
use App\Domain\Checkout\ShippingOption;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\Presenters\CheckoutStatePresenter;
use App\Http\Responses\ProblemDetailsResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

/**
 * Handles checkout shipping selection for the public API.
 */
final class CheckoutShippingOptionController extends Controller
{
    /**
     * Create the shipping option controller.
     */
    public function __construct(
        private readonly CheckoutManager $checkout,
        private readonly CheckoutStatePresenter $presenter,
        private readonly ProblemDetailsResponse $problems,
    ) {}

    /**
     * Select a shipping option.
     */
    public function put(Request $request): JsonResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $validated = $request->validate([
            'checkoutId' => ['required', 'string'],
            'shippingOption' => ['required', new Enum(ShippingOption::class)],
        ]);

        $checkout = $this->checkout->selectShippingOption(
            tenant: $tenant,
            checkoutId: (string) $validated['checkoutId'],
            option: ShippingOption::from((string) $validated['shippingOption']),
        );

        if ($checkout === null) {
            return $this->problems->make(
                request: $request,
                tenant: $tenant,
                type: 'checkout-state-not-found',
                title: 'Checkout state not found',
                status: 404,
                detail: 'The checkout state is not visible to this tenant.',
            );
        }

        return response()->json($this->presenter->state($tenant, $checkout));
    }
}
