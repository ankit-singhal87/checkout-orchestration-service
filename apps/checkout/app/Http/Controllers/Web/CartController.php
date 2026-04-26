<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Cart\CartReader;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\ViewModels\CartViewModelFactory;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CartController extends Controller
{
    public function __construct(
        private readonly CartReader $carts,
        private readonly CartViewModelFactory $viewModels,
    ) {}

    public function show(Request $request): View
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $cartId = $request->session()->get('cart_id');

        return view('cart.show', [
            'view' => $this->viewModels->make(
                tenant: $tenant,
                cart: is_string($cartId) ? $this->carts->cartForSession($tenant, $cartId) : null,
            ),
        ]);
    }
}
