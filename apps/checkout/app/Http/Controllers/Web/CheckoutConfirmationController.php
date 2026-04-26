<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Checkout\CheckoutManager;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\ViewModels\CheckoutViewModelFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles idempotent guest checkout confirmation.
 */
final class CheckoutConfirmationController extends Controller
{
    /**
     * Create the confirmation controller.
     */
    public function __construct(
        private readonly CheckoutManager $checkout,
        private readonly CheckoutViewModelFactory $viewModels,
    ) {}

    /**
     * Confirm the current checkout state and create an order.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $checkoutId = (string) $request->session()->get('checkout_id', '');
        $idempotencyKey = (string) $request->input(
            'idempotency_key',
            $request->session()->get('checkout_idempotency_key', ''),
        );

        $order = $this->checkout->confirm($tenant, $checkoutId, $idempotencyKey);

        if ($order === null) {
            return redirect()->route('checkout.show')->withErrors([
                'confirmation' => 'Complete address, shipping, and payment before confirming.',
            ]);
        }

        return redirect()->route('checkout.confirmation.show', ['orderRef' => $order->order_ref]);
    }

    /**
     * Show the confirmation page for a committed order.
     */
    public function show(Request $request, string $orderRef): View
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $order = $this->checkout->orderForTenant($tenant, $orderRef);

        abort_if($order === null, 404);

        return view('checkout.confirmation', [
            'view' => $this->viewModels->confirmation($tenant, $order),
        ]);
    }
}
