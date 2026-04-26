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
 * Handles checkout address updates for the public API.
 */
final class CheckoutAddressController extends Controller
{
    /**
     * Create the address controller.
     */
    public function __construct(
        private readonly CheckoutManager $checkout,
        private readonly CheckoutStatePresenter $presenter,
        private readonly ProblemDetailsResponse $problems,
    ) {}

    /**
     * Update the shipping address for a checkout state.
     */
    public function put(Request $request): JsonResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $validated = $request->validate([
            'checkoutId' => ['required', 'string'],
            'shippingAddress.name' => ['required', 'string'],
            'shippingAddress.line1' => ['required', 'string'],
            'shippingAddress.postalCode' => ['required', 'string'],
            'shippingAddress.city' => ['required', 'string'],
            'shippingAddress.country' => ['required', 'string', 'size:2'],
        ]);

        /** @var array{name: string, line1: string, postalCode: string, city: string, country: string} $address */
        $address = $validated['shippingAddress'];
        $checkout = $this->checkout->updateAddress($tenant, (string) $validated['checkoutId'], [
            'name' => $address['name'],
            'line1' => $address['line1'],
            'postal_code' => $address['postalCode'],
            'city' => $address['city'],
            'country' => strtoupper($address['country']),
        ]);

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
