<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Checkout\CheckoutManager;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\ViewModels\CheckoutViewModelFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Renders the guest checkout state screen.
 */
final class CheckoutController extends Controller
{
    /**
     * Create the checkout page controller.
     */
    public function __construct(
        private readonly CheckoutManager $checkout,
        private readonly CheckoutViewModelFactory $viewModels,
    ) {}

    /**
     * Start or resume checkout for the current cart.
     */
    public function show(Request $request): View|RedirectResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $cartId = $request->session()->get('cart_id');

        if (! is_string($cartId)) {
            return redirect()->route('cart.show');
        }

        $checkout = $this->checkout->startForCart($tenant, $cartId);

        if ($checkout === null) {
            return redirect()->route('cart.show');
        }

        $request->session()->put('checkout_id', $checkout->checkout_id);
        $idempotencyKey = (string) $request->session()->get('checkout_idempotency_key', 'idem_'.Str::uuid()->toString());
        $request->session()->put('checkout_idempotency_key', $idempotencyKey);

        return view('checkout.show', [
            'view' => $this->viewModels->checkout($tenant, $checkout, $idempotencyKey),
        ]);
    }
}
