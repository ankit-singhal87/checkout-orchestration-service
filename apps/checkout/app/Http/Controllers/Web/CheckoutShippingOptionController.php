<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Checkout\CheckoutManager;
use App\Domain\Checkout\ShippingOption;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
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
    public function __construct(private readonly CheckoutManager $checkout) {}

    /**
     * Store the selected shipping option.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $checkoutId = (string) $request->session()->get('checkout_id', '');
        $validated = $request->validate([
            'shipping_option' => ['required', 'string'],
        ]);

        $option = ShippingOption::tryFrom((string) $validated['shipping_option']);
        abort_if($option === null, 422);

        $this->checkout->selectShippingOption($tenant, $checkoutId, $option);

        return redirect()->route('checkout.show');
    }
}
