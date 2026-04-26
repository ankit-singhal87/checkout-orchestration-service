<?php

use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CartItemController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ShopController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/shop')->name('home');

Route::middleware('tenant')->group(function (): void {
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('shop.product.show');
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('/cart/items', [CartItemController::class, 'store'])->name('cart.items.store');
});
