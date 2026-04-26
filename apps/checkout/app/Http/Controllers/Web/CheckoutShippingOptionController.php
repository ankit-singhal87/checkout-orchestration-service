<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Checkout\CheckoutManager;
use App\Domain\Checkout\ShippingOption;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\Responses\WebProblemDetailsResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles shipping option selection for guest checkout.
 */
final class CheckoutShippingOptionController extends Controller
{
    /**
     * Create the shipping option controller.
     */
    public function __construct(
        private readonly CheckoutManager $checkout,
        private readonly WebProblemDetailsResponse $problems,
    ) {}

    /**
     * Store the selected shipping option.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $checkoutId = (string) $request->session()->get('checkout_id', '');
        $validated = $request->validate([
            'shipping_option' => ['required', 'string'],
        ]);

        $option = ShippingOption::tryFrom((string) $validated['shipping_option']);

        if ($option === null) {
            if ($request->expectsJson()) {
                return $this->problems->make(
                    request: $request,
                    tenant: $tenant,
                    type: 'validation-failed',
                    title: 'Validation failed',
                    status: 422,
                    detail: 'Request fields failed validation.',
                    errors: [[
                        'field' => 'shipping_option',
                        'code' => 'invalid',
                        'message' => 'Select a supported shipping option.',
                    ]],
                );
            }

            abort(422);
        }

        $this->checkout->selectShippingOption($tenant, $checkoutId, $option);

        return redirect()->route('checkout.show');
    }
}
