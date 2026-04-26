<x-layouts.shop
    title="Checkout"
    :tenant-name="$view->tenant->displayName"
    :tenant-color="$view->tenant->primaryColor"
>
    <section class="page-head">
        <p class="eyebrow">Checkout</p>
        <h1>Complete your order</h1>
        <p class="lede">Guest checkout with deterministic state, tenant isolation, and idempotent order confirmation.</p>
    </section>

    @if ($errors->any())
        <section class="card panel alert" role="alert">
            <strong>Checkout needs attention.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="split">
        <div class="stack">
            <section class="card panel stack">
                <p class="eyebrow">Step 1</p>
                <h2>Shipping address</h2>
                <form class="form-grid" method="post" action="{{ route('checkout.address.update') }}">
                    @csrf
                    <label class="full">
                        Name
                        <input name="name" value="{{ old('name', $view->shippingAddress['name'] ?? 'Demo Shopper') }}" required>
                    </label>
                    <label class="full">
                        Address
                        <input name="line1" value="{{ old('line1', $view->shippingAddress['line1'] ?? 'Demo Street 1') }}" required>
                    </label>
                    <label>
                        Postal code
                        <input name="postal_code" value="{{ old('postal_code', $view->shippingAddress['postal_code'] ?? '10115') }}" required>
                    </label>
                    <label>
                        City
                        <input name="city" value="{{ old('city', $view->shippingAddress['city'] ?? 'Berlin') }}" required>
                    </label>
                    <label>
                        Country
                        <input name="country" value="{{ old('country', $view->shippingAddress['country'] ?? 'DE') }}" maxlength="2" required>
                    </label>
                    <span></span>
                    <button class="button" type="submit">Save address</button>
                </form>
            </section>

            <section class="card panel stack">
                <p class="eyebrow">Step 2</p>
                <h2>Shipping</h2>
                @foreach ($view->shippingOptions as $option)
                    <form method="post" action="{{ route('checkout.shipping-option.update') }}">
                        @csrf
                        <input type="hidden" name="shipping_option" value="{{ $option['id'] }}">
                        <button class="button {{ $option['selected'] ? '' : 'secondary' }}" type="submit">
                            {{ $option['selected'] ? 'Selected: ' : 'Select ' }}{{ $option['label'] }} &middot; {{ $option['priceLabel'] }}
                        </button>
                    </form>
                @endforeach
            </section>

            <section class="card panel stack">
                <p class="eyebrow">Step 3</p>
                <h2>Payment</h2>
                @foreach ($view->paymentMethods as $method)
                    <form method="post" action="{{ route('checkout.payment-method.update') }}">
                        @csrf
                        <input type="hidden" name="payment_method" value="{{ $method['id'] }}">
                        <button class="button {{ $method['selected'] ? '' : 'secondary' }}" type="submit">
                            {{ $method['selected'] ? 'Selected: ' : 'Select ' }}{{ $method['label'] }}
                        </button>
                    </form>
                @endforeach
            </section>
        </div>

        <aside class="card panel stack">
            <div>
                <p class="eyebrow">Order summary</p>
                <h2>Status: {{ $view->status }}</h2>
            </div>

            @foreach ($view->items as $item)
                <article class="row">
                    <span>
                        <strong>{{ $item['productName'] }}</strong><br>
                        <span class="muted">{{ $item['variantLabel'] }} &middot; Quantity {{ $item['quantity'] }}</span>
                    </span>
                    <strong>{{ $item['priceLabel'] }}</strong>
                </article>
            @endforeach

            <div class="stack">
                <div class="row"><span>Subtotal</span><strong>{{ $view->totals['subtotal'] }}</strong></div>
                <div class="row"><span>Shipping</span><strong>{{ $view->totals['shipping'] }}</strong></div>
                <div class="row"><span>Total</span><strong>{{ $view->totals['total'] }}</strong></div>
            </div>

            <form method="post" action="{{ route('checkout.confirm') }}">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ $view->idempotencyKey }}">
                <button class="button" type="submit" @disabled(! $view->canConfirm)>Confirm order</button>
            </form>
        </aside>
    </section>
</x-layouts.shop>
