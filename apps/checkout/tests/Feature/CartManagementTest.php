<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

it('adds an in-stock tenant variant to the cart', function () {
    $variant = ProductVariantRecord::query()
        ->where('variant_id', 'fashion-variant-001-purple-s')
        ->firstOrFail();

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post('http://fashion-demo.localhost/cart/items', ['variant_id' => $variant->variant_id])
        ->assertRedirect('/cart');

    $this->assertDatabaseHas('cart_items', [
        'product_variant_record_id' => $variant->id,
        'quantity' => 1,
    ]);

    $this
        ->get('http://fashion-demo.localhost/cart')
        ->assertOk()
        ->assertSee('Rain Ready Shell Jacket')
        ->assertSee('purple')
        ->assertSee('S')
        ->assertSee('easy returns');
});

it('rejects a variant from another tenant with Problem Details', function () {
    $variant = ProductVariantRecord::query()
        ->where('variant_id', 'sports-variant-001-teal-one-size')
        ->firstOrFail();

    $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->postJson('http://fashion-demo.localhost/cart/items', ['variant_id' => $variant->variant_id])
        ->assertForbidden()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'https://checkout.example.test/problems/tenant-access-denied')
        ->assertJsonPath('status', 403)
        ->assertJsonPath('tenant', 'fashion-store')
        ->assertJsonPath('shop', 'fashion-main');

    $this->assertDatabaseMissing('cart_items', [
        'product_variant_record_id' => $variant->id,
    ]);
});
