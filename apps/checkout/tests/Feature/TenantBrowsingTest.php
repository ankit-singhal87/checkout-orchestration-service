<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\TenantRecord;

it('shows only fashion tenant products on the fashion host', function () {
    $this->assertDatabaseHas('tenants', ['tenant_id' => 'fashion-store']);

    $this
        ->get('http://fashion-demo.localhost/shop')
        ->assertOk()
        ->assertSee('Fashion Store')
        ->assertSee('Rain Ready Shell Jacket')
        ->assertSee('easy returns')
        ->assertDontSee('Trail Runner Pack')
        ->assertDontSee('Sports Outlet');
});

it('shows only sports tenant products on the sports host', function () {
    $this->assertDatabaseHas('tenants', ['tenant_id' => 'sports-outlet']);

    $this
        ->get('http://sports-demo.localhost/shop')
        ->assertOk()
        ->assertSee('Sports Outlet')
        ->assertSee('Trail Runner Pack')
        ->assertSee('stock confidence')
        ->assertDontSee('Rain Ready Shell Jacket')
        ->assertDontSee('Fashion Store');
});

it('fails closed for an unknown tenant host', function () {
    $this
        ->get('http://unknown-demo.localhost/shop')
        ->assertNotFound();
});

it('does not show another tenant product detail by slug', function () {
    expect(TenantRecord::query()->count())->toBeGreaterThan(1);

    $this
        ->get('http://fashion-demo.localhost/product/trail-runner-pack')
        ->assertNotFound();
});
