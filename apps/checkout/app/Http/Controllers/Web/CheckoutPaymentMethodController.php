<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Checkout\CheckoutManager;
use App\Domain\Checkout\PaymentMethod;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
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
    public function __construct(private readonly CheckoutManager $checkout) {}

    /**
     * Store the selected simulated payment method.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $checkoutId = (string) $request->session()->get('checkout_id', '');
        $validated = $request->validate([
            'payment_method' => ['required', 'string'],
        ]);

        $method = PaymentMethod::tryFrom((string) $validated['payment_method']);
        abort_if($method === null, 422);

        $this->checkout->selectPaymentMethod($tenant, $checkoutId, $method);

        return redirect()->route('checkout.show');
    }
}
