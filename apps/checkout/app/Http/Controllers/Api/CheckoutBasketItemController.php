<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Checkout\CheckoutManager;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\Presenters\CheckoutStatePresenter;
use App\Http\Responses\ProblemDetailsResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles checkout basket item quantity changes for the public API.
 */
final class CheckoutBasketItemController extends Controller
{
    /**
     * Create the basket item controller.
     */
    public function __construct(
        private readonly CheckoutManager $checkout,
        private readonly CheckoutStatePresenter $presenter,
        private readonly ProblemDetailsResponse $problems,
    ) {}

    /**
     * Update a basket item quantity. Quantity zero removes the item.
     */
    public function put(Request $request, string $variantId): JsonResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $validated = $request->validate([
            'checkoutId' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $checkout = $this->checkout->updateBasketItemQuantity(
            tenant: $tenant,
            checkoutId: (string) $validated['checkoutId'],
            variantId: $variantId,
            quantity: (int) $validated['quantity'],
        );

        if ($checkout === null) {
            return $this->problems->make(
                request: $request,
                tenant: $tenant,
                type: 'checkout-state-not-found',
                title: 'Checkout state not found',
                status: 404,
                detail: 'The checkout state or basket item is not visible to this tenant.',
            );
        }

        return response()->json($this->presenter->state($tenant, $checkout));
    }
}
