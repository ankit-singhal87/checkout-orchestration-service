<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\OrderRecord;
use App\Infrastructure\Persistence\Eloquent\OutboxEventRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

it('lets a guest shopper confirm an order', function () {
    $variant = ProductVariantRecord::query()
        ->where('variant_id', 'fashion-variant-001-purple-s')
        ->firstOrFail();

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/cart/items', ['variant_id' => $variant->variant_id])
        ->assertRedirect('/cart');

    $this
        ->get('http://fashion-demo.localhost/checkout')
        ->assertOk()
        ->assertSee('Checkout')
        ->assertSee('Rain Ready Shell Jacket')
        ->assertSee('Confirm order');

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/checkout/address', [
            'name' => 'Demo Shopper',
            'line1' => 'Demo Street 1',
            'postal_code' => '10115',
            'city' => 'Berlin',
            'country' => 'DE',
        ])
        ->assertRedirect('/checkout');

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/checkout/shipping-option', ['shipping_option' => 'standard'])
        ->assertRedirect('/checkout');

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/checkout/payment-method', ['payment_method' => 'invoice'])
        ->assertRedirect('/checkout');

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/checkout/confirm', ['idempotency_key' => checkoutTestNamespace('confirm-order')])
        ->assertRedirect();

    expect(OrderRecord::query()->count())->toBe(1)
        ->and(OutboxEventRecord::query()->where('event_type', 'order.confirmed')->count())->toBe(1);

    $order = OrderRecord::query()->firstOrFail();

    $this
        ->get('http://fashion-demo.localhost/checkout/confirmation/'.$order->order_ref)
        ->assertOk()
        ->assertSee('Order confirmed')
        ->assertSee($order->order_ref);
});

it('reuses the original order for the same idempotency key', function () {
    $variant = ProductVariantRecord::query()
        ->where('variant_id', 'fashion-variant-001-purple-s')
        ->firstOrFail();

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/cart/items', ['variant_id' => $variant->variant_id]);

    $this->get('http://fashion-demo.localhost/checkout');

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/checkout/address', [
            'name' => 'Demo Shopper',
            'line1' => 'Demo Street 1',
            'postal_code' => '10115',
            'city' => 'Berlin',
            'country' => 'DE',
        ]);

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/checkout/shipping-option', ['shipping_option' => 'standard']);

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/checkout/payment-method', ['payment_method' => 'invoice']);

    $idempotencyKey = checkoutTestNamespace('same-order');

    $first = $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/checkout/confirm', ['idempotency_key' => $idempotencyKey]);

    $second = $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/checkout/confirm', ['idempotency_key' => $idempotencyKey]);

    expect(OrderRecord::query()->count())->toBe(1)
        ->and($first->headers->get('Location'))->toBe($second->headers->get('Location'));
});

it('does not confirm checkout before required selections are complete', function () {
    $variant = ProductVariantRecord::query()
        ->where('variant_id', 'fashion-variant-001-purple-s')
        ->firstOrFail();

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/cart/items', ['variant_id' => $variant->variant_id]);

    $this->get('http://fashion-demo.localhost/checkout');

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/checkout/confirm', ['idempotency_key' => checkoutTestNamespace('early-confirm')])
        ->assertRedirect('/checkout')
        ->assertSessionHasErrors('confirmation');

    expect(OrderRecord::query()->count())->toBe(0);
});

it('returns Problem Details for JSON checkout confirmation conflicts', function () {
    $variant = ProductVariantRecord::query()
        ->where('variant_id', 'fashion-variant-001-purple-s')
        ->firstOrFail();

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/cart/items', ['variant_id' => $variant->variant_id]);

    $this->get('http://fashion-demo.localhost/checkout');

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->postJson('http://fashion-demo.localhost/checkout/confirm', ['idempotency_key' => checkoutTestNamespace('json-early-confirm')])
        ->assertConflict()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'https://checkout.example.test/problems/checkout-state-conflict')
        ->assertJsonPath('status', 409)
        ->assertJsonPath('tenant', 'fashion-store')
        ->assertJsonPath('shop', 'fashion-main');

    expect(OrderRecord::query()->count())->toBe(0);
});

it('returns Problem Details for invalid JSON checkout selections', function () {
    $variant = ProductVariantRecord::query()
        ->where('variant_id', 'fashion-variant-001-purple-s')
        ->firstOrFail();

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/cart/items', ['variant_id' => $variant->variant_id]);

    $this->get('http://fashion-demo.localhost/checkout');

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->postJson('http://fashion-demo.localhost/checkout/shipping-option', ['shipping_option' => 'same-day-drone'])
        ->assertUnprocessable()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'https://checkout.example.test/problems/validation-failed')
        ->assertJsonPath('errors.0.field', 'shipping_option');

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->postJson('http://fashion-demo.localhost/checkout/payment-method', ['payment_method' => 'crypto'])
        ->assertUnprocessable()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'https://checkout.example.test/problems/validation-failed')
        ->assertJsonPath('errors.0.field', 'payment_method');
});
