<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Checkout\CheckoutManager;
use App\Domain\Checkout\PaymentMethod;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\Responses\WebProblemDetailsResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles payment method selection for guest checkout.
 */
final class CheckoutPaymentMethodController extends Controller
{
    /**
     * Create the payment method controller.
     */
    public function __construct(
        private readonly CheckoutManager $checkout,
        private readonly WebProblemDetailsResponse $problems,
    ) {}

    /**
     * Store the selected simulated payment method.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $checkoutId = (string) $request->session()->get('checkout_id', '');
        $validated = $request->validate([
            'payment_method' => ['required', 'string'],
        ]);

        $method = PaymentMethod::tryFrom((string) $validated['payment_method']);

        if ($method === null) {
            if ($request->expectsJson()) {
                return $this->problems->make(
                    request: $request,
                    tenant: $tenant,
                    type: 'validation-failed',
                    title: 'Validation failed',
                    status: 422,
                    detail: 'Request fields failed validation.',
                    errors: [[
                        'field' => 'payment_method',
                        'code' => 'invalid',
                        'message' => 'Select a supported payment method.',
                    ]],
                );
            }

            abort(422);
        }

        $this->checkout->selectPaymentMethod($tenant, $checkoutId, $method);

        return redirect()->route('checkout.show');
    }
}
