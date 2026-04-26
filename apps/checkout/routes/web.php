<?php

use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CartItemController;
use App\Http\Controllers\Web\CheckoutAddressController;
use App\Http\Controllers\Web\CheckoutConfirmationController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\CheckoutPaymentMethodController;
use App\Http\Controllers\Web\CheckoutShippingOptionController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ShopController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/shop')->name('home');

Route::middleware('tenant')->group(function (): void {
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('shop.product.show');
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('/cart/items', [CartItemController::class, 'store'])->name('cart.items.store');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/address', [CheckoutAddressController::class, 'update'])->name('checkout.address.update');
    Route::post('/checkout/shipping-option', [CheckoutShippingOptionController::class, 'update'])->name('checkout.shipping-option.update');
    Route::post('/checkout/payment-method', [CheckoutPaymentMethodController::class, 'update'])->name('checkout.payment-method.update');
    Route::post('/checkout/confirm', [CheckoutConfirmationController::class, 'store'])->name('checkout.confirm');
    Route::get('/checkout/confirmation/{orderRef}', [CheckoutConfirmationController::class, 'show'])->name('checkout.confirmation.show');
});
