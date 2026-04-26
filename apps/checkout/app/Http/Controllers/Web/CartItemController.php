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

/**
 * Handles cart item mutations for the tenant-scoped web checkout.
 */
final class CartItemController extends Controller
{
    /**
     * Create the cart item controller.
     */
    public function __construct(private readonly AddCartItem $cartItems) {}

    /**
     * Add a submitted variant to the current shopper cart.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $variantId = (string) $request->string('variant_id');
        $cartId = (string) $request->session()->get('cart_id', 'cart_'.Str::uuid()->toString());

        $request->session()->put('cart_id', $cartId);

        $result = $this->cartItems->add($tenant, $cartId, $variantId);

        if (! $result->isAdded()) {
            $problemStatus = $result->problemStatus() ?? 400;

            $payload = [
                'type' => 'https://checkout.example.test'.$result->problemType(),
                'title' => $result->problemTitle(),
                'status' => $problemStatus,
                'detail' => $result->problemDetail(),
                'instance' => '/'.$request->path(),
                'traceId' => $request->headers->get('X-Trace-Id', $request->headers->get('X-Request-Id', '')),
                'tenant' => $tenant->tenantId,
                'shop' => $tenant->shopId,
                'errors' => [],
            ];

            return response()
                ->json($payload, $problemStatus)
                ->header('Content-Type', 'application/problem+json');
        }

        return redirect()->route('cart.show');
    }
}
