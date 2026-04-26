<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Cart\AddCartItem;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class CartItemController extends Controller
{
    public function __construct(private readonly AddCartItem $cartItems) {}

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $variantId = (string) $request->string('variant_id');
        $cartId = (string) $request->session()->get('cart_id', 'cart_'.Str::uuid()->toString());

        $request->session()->put('cart_id', $cartId);

        $result = $this->cartItems->add($tenant, $cartId, $variantId);

        if (! $result->added) {
            $payload = [
                'type' => 'https://checkout.example.test'.$result->problemType,
                'title' => $result->problemTitle,
                'status' => $result->problemStatus,
                'detail' => $result->problemDetail,
                'instance' => $request->path(),
                'traceId' => $request->headers->get('X-Request-Id', ''),
                'tenant' => $tenant->tenantId,
                'shop' => $tenant->shopId,
                'errors' => [],
            ];

            return response()
                ->json($payload, $result->problemStatus ?? 400)
                ->header('Content-Type', 'application/problem+json');
        }

        return redirect()->route('cart.show');
    }
}
