<x-layouts.shop
    title="Cart"
    :tenant-name="$view->tenant->displayName"
    :tenant-color="$view->tenant->primaryColor"
>
    <section class="page-head">
        <p class="eyebrow">Cart</p>
        <h1>Your cart</h1>
    </section>

    <section class="card panel stack">
        @if ($view->items === [])
            <p class="lede">Your cart is empty.</p>
            <p><a class="button" href="{{ route('shop.index') }}">Browse products</a></p>
        @else
            @foreach ($view->items as $item)
                <article class="row">
                    <span>
                        <strong>{{ $item['productName'] }}</strong><br>
                        <span class="muted">{{ $item['variantLabel'] }} &middot; Quantity {{ $item['quantity'] }}</span>
                    </span>
                    <strong>{{ $item['priceLabel'] }}</strong>
                </article>
            @endforeach
        @endif

        <ul class="badges" aria-label="Cart trust badges">
            @foreach ($view->tenant->trustBadges as $badge)
                <li class="badge">{{ $badge }}</li>
            @endforeach
        </ul>

        @if ($view->items !== [])
            <p>
                <a class="button" href="{{ route('checkout.show') }}">Continue to checkout</a>
            </p>
        @endif
    </section>
</x-layouts.shop>
