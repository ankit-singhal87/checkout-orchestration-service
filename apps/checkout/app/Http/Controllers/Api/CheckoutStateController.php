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
 * Creates, resumes, and reads tenant-scoped checkout state.
 */
final class CheckoutStateController extends Controller
{
    /**
     * Create the checkout state controller.
     */
    public function __construct(
        private readonly CheckoutManager $checkout,
        private readonly CheckoutStatePresenter $presenter,
        private readonly ProblemDetailsResponse $problems,
    ) {}

    /**
     * Start or resume checkout state from a cart handle.
     */
    public function put(Request $request): JsonResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $validated = $request->validate([
            'cartId' => ['required', 'string'],
        ]);

        $checkout = $this->checkout->startForCart($tenant, (string) $validated['cartId']);

        if ($checkout === null) {
            return $this->problems->make(
                request: $request,
                tenant: $tenant,
                type: 'checkout-state-not-found',
                title: 'Checkout state not found',
                status: 404,
                detail: 'The cart has no checkout state visible to this tenant.',
            );
        }

        return response()->json($this->presenter->state($tenant, $checkout));
    }

    /**
     * Return the current checkout state.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $checkoutId = (string) $request->query('checkoutId', '');
        $checkout = $this->checkout->checkoutForTenant($tenant, $checkoutId);

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
