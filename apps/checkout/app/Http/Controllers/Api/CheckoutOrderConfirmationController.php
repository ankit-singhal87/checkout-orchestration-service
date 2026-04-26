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

        $order = $this->checkout->confirm(
            tenant: $tenant,
            checkoutId: (string) $validated['checkoutId'],
            idempotencyKey: (string) $validated['idempotencyKey'],
        );

        if ($order === null) {
            return $this->problems->make(
                request: $request,
                tenant: $tenant,
                type: 'checkout-state-conflict',
                title: 'Checkout state conflict',
                status: 409,
                detail: 'Complete address, shipping, and payment before confirming.',
            );
        }

        return response()->json($this->presenter->order($tenant, $order));
    }
}
