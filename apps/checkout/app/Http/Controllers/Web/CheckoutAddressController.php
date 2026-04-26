<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Checkout\CheckoutManager;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles shipping address updates for guest checkout.
 */
final class CheckoutAddressController extends Controller
{
    /**
     * Create the checkout address controller.
     */
    public function __construct(private readonly CheckoutManager $checkout) {}

    /**
     * Validate and store the submitted shipping address.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $checkoutId = (string) $request->session()->get('checkout_id', '');
        $address = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'line1' => ['required', 'string', 'max:160'],
            'postal_code' => ['required', 'string', 'max:32'],
            'city' => ['required', 'string', 'max:80'],
            'country' => ['required', 'string', 'size:2'],
        ]);

        $this->checkout->updateAddress($tenant, $checkoutId, $address);

        return redirect()->route('checkout.show');
    }
}
