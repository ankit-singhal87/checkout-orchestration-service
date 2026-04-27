<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Checkout\CheckoutManager;
use App\Application\Checkout\ConfirmCheckoutStatus;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\Presenters\CheckoutStatePresenter;
use App\Http\Responses\ProblemDetailsResponse;
use App\Infrastructure\Persistence\Eloquent\OrderRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles idempotent checkout order confirmation for the public API.
 */
final class CheckoutOrderConfirmationController extends Controller
{
    /**
     * Create the order confirmation controller.
     */
    public function __construct(
        private readonly CheckoutManager $checkout,
        private readonly CheckoutStatePresenter $presenter,
        private readonly ProblemDetailsResponse $problems,
    ) {}

    /**
     * Confirm a checkout state and return the committed order.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $validated = $request->validate([
            'checkoutId' => ['required', 'string'],
            'idempotencyKey' => ['required', 'string'],
        ]);

        $result = $this->checkout->confirmOrder(
            tenant: $tenant,
            checkoutId: (string) $validated['checkoutId'],
            idempotencyKey: (string) $validated['idempotencyKey'],
        );

        if ($result->status === ConfirmCheckoutStatus::NotFound) {
            return $this->problems->make(
                request: $request,
                tenant: $tenant,
                type: 'checkout-state-not-found',
                title: 'Checkout state not found',
                status: 404,
                detail: 'The checkout state is not visible to this tenant.',
            );
        }

        if ($result->status === ConfirmCheckoutStatus::NotReady) {
            return $this->problems->make(
                request: $request,
                tenant: $tenant,
                type: 'checkout-state-conflict',
                title: 'Checkout state conflict',
                status: 409,
                detail: 'Complete address, shipping, and payment before confirming.',
            );
        }

        if ($result->status === ConfirmCheckoutStatus::IdempotencyConflict) {
            return $this->problems->make(
                request: $request,
                tenant: $tenant,
                type: 'checkout-state-conflict',
                title: 'Checkout state conflict',
                status: 409,
                detail: 'The checkout confirmation conflicts with an existing order. Refresh the checkout before retrying.',
            );
        }

        $order = $result->order;
        assert($order instanceof OrderRecord);

        return response()->json($this->presenter->order($tenant, $order));
    }
}
