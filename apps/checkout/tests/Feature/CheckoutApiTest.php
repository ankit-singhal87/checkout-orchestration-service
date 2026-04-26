<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\CartItemRecord;
use App\Infrastructure\Persistence\Eloquent\CartRecord;
use App\Infrastructure\Persistence\Eloquent\OrderRecord;
use App\Infrastructure\Persistence\Eloquent\OutboxEventRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;
use App\Infrastructure\Persistence\Eloquent\TenantRecord;

it('returns tenant checkout configuration', function () {
    $this
        ->getJson('http://fashion-demo.localhost/api/checkout/config')
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=60, public')
        ->assertJsonPath('tenant.id', 'fashion-store')
        ->assertJsonPath('tenant.shop', 'fashion-main')
        ->assertJsonPath('features.guestCheckout', true);
});

it('creates and reads a checkout state for a tenant cart', function () {
    $cart = apiCheckoutCart('fashion-store', 'fashion-variant-001-purple-s');

    $response = $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state', ['cartId' => $cart->cart_id])
        ->assertOk()
        ->assertJsonPath('tenant', 'fashion-store')
        ->assertJsonPath('shop', 'fashion-main')
        ->assertJsonPath('status', 'created')
        ->assertJsonPath('basket.items.0.productName', 'Rain Ready Shell Jacket');

    $checkoutId = $response->json('checkoutId');

    $this
        ->getJson('http://fashion-demo.localhost/api/checkout/state?checkoutId='.$checkoutId)
        ->assertOk()
        ->assertJsonPath('checkoutId', $checkoutId)
        ->assertJsonPath('basket.items.0.variantId', 'fashion-variant-001-purple-s');
});

it('confirms an API checkout idempotently', function () {
    $cart = apiCheckoutCart('fashion-store', 'fashion-variant-001-purple-s');
    $checkoutId = $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state', ['cartId' => $cart->cart_id])
        ->json('checkoutId');

    $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state/address', [
            'checkoutId' => $checkoutId,
            'shippingAddress' => [
                'name' => 'API Shopper',
                'line1' => 'API Street 1',
                'postalCode' => '10115',
                'city' => 'Berlin',
                'country' => 'DE',
            ],
        ])
        ->assertOk()
        ->assertJsonPath('status', 'addressed');

    $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state/shipping-option', [
            'checkoutId' => $checkoutId,
            'shippingOption' => 'standard',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'shipping_selected');

    $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state/payment-method', [
            'checkoutId' => $checkoutId,
            'paymentMethod' => 'invoice',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'payment_selected')
        ->assertJsonPath('nextActions.0', 'confirm_order');

    $idempotencyKey = checkoutTestNamespace('api-order-confirmation');

    $first = $this
        ->postJson('http://fashion-demo.localhost/api/checkout/state/order-confirmation', [
            'checkoutId' => $checkoutId,
            'idempotencyKey' => $idempotencyKey,
        ])
        ->assertOk()
        ->assertJsonPath('tenant', 'fashion-store')
        ->assertJsonPath('status', 'confirmed');

    $second = $this
        ->postJson('http://fashion-demo.localhost/api/checkout/state/order-confirmation', [
            'checkoutId' => $checkoutId,
            'idempotencyKey' => $idempotencyKey,
        ])
        ->assertOk();

    expect($first->json('orderRef'))->toBe($second->json('orderRef'))
        ->and(OrderRecord::query()->count())->toBe(1)
        ->and(OutboxEventRecord::query()->where('event_type', 'order.confirmed')->count())->toBe(1);
});

it('updates basket item quantity and invalidates dependent selections', function () {
    $cart = apiCheckoutCart('fashion-store', 'fashion-variant-001-purple-s');
    $checkoutId = $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state', ['cartId' => $cart->cart_id])
        ->json('checkoutId');

    $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state/address', [
            'checkoutId' => $checkoutId,
            'shippingAddress' => [
                'name' => 'API Shopper',
                'line1' => 'API Street 1',
                'postalCode' => '10115',
                'city' => 'Berlin',
                'country' => 'DE',
            ],
        ])
        ->assertOk();

    $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state/shipping-option', [
            'checkoutId' => $checkoutId,
            'shippingOption' => 'standard',
        ])
        ->assertOk();

    $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state/payment-method', [
            'checkoutId' => $checkoutId,
            'paymentMethod' => 'invoice',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'payment_selected');

    $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state/basket/items/fashion-variant-001-purple-s', [
            'checkoutId' => $checkoutId,
            'quantity' => 2,
        ])
        ->assertOk()
        ->assertJsonPath('status', 'addressed')
        ->assertJsonPath('basket.items.0.variantId', 'fashion-variant-001-purple-s')
        ->assertJsonPath('basket.items.0.quantity', 2)
        ->assertJsonPath('shippingOptions.0.selected', false)
        ->assertJsonPath('paymentMethods.0.selected', false)
        ->assertJsonPath('totals.subtotal', 17990)
        ->assertJsonPath('totals.shipping', 0)
        ->assertJsonPath('totals.total', 17990);
});

it('removes basket items with zero quantity', function () {
    $cart = apiCheckoutCart('fashion-store', 'fashion-variant-001-purple-s');
    $checkoutId = $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state', ['cartId' => $cart->cart_id])
        ->json('checkoutId');

    $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state/basket/items/fashion-variant-001-purple-s', [
            'checkoutId' => $checkoutId,
            'quantity' => 0,
        ])
        ->assertOk()
        ->assertJsonPath('status', 'created')
        ->assertJsonPath('basket.items', [])
        ->assertJsonPath('totals.subtotal', 0)
        ->assertJsonPath('totals.total', 0);
});

it('returns Problem Details for another tenant checkout state', function () {
    $cart = apiCheckoutCart('sports-outlet', 'sports-variant-001-teal-one-size');
    $checkoutId = $this
        ->putJson('http://sports-demo.localhost/api/checkout/state', ['cartId' => $cart->cart_id])
        ->json('checkoutId');

    $this
        ->getJson('http://fashion-demo.localhost/api/checkout/state?checkoutId='.$checkoutId)
        ->assertNotFound()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'https://checkout.example.test/problems/checkout-state-not-found')
        ->assertJsonPath('status', 404)
        ->assertJsonPath('tenant', 'fashion-store')
        ->assertJsonPath('shop', 'fashion-main');
});

it('returns Problem Details for API validation failures', function () {
    $this
        ->putJson('http://fashion-demo.localhost/api/checkout/state', [])
        ->assertUnprocessable()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'https://checkout.example.test/problems/validation-failed')
        ->assertJsonPath('status', 422)
        ->assertJsonPath('tenant', 'fashion-store')
        ->assertJsonPath('shop', 'fashion-main')
        ->assertJsonPath('errors.0.field', 'cartId');
});

function apiCheckoutCart(string $tenantId, string $variantId): CartRecord
{
    $tenant = TenantRecord::query()
        ->where('tenant_id', $tenantId)
        ->firstOrFail();
    $variant = ProductVariantRecord::query()
        ->where('tenant_record_id', $tenant->id)
        ->where('variant_id', $variantId)
        ->firstOrFail();

    /** @var CartRecord $cart */
    $cart = CartRecord::query()->create([
        'tenant_record_id' => $tenant->id,
        'cart_id' => checkoutTestNamespace($tenantId.'-'.$variantId),
    ]);

    CartItemRecord::query()->create([
        'cart_record_id' => $cart->id,
        'product_variant_record_id' => $variant->id,
        'quantity' => 1,
    ]);

    return $cart;
}
