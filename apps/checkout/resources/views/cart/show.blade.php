<x-layouts.shop
    title="Cart"
    :tenant-name="$view->tenant->displayName"
    :tenant-color="$view->tenant->primaryColor"
>
    <section class="card">
        <h2>Your cart</h2>

        @if ($view->items === [])
            <p>Your cart is empty.</p>
        @else
            @foreach ($view->items as $item)
                <article>
                    <h3>{{ $item['productName'] }}</h3>
                    <p>{{ $item['variantLabel'] }}</p>
                    <p>Quantity: {{ $item['quantity'] }}</p>
                    <p>{{ $item['priceLabel'] }}</p>
                </article>
            @endforeach
        @endif

        <ul class="badges" aria-label="Cart trust badges">
            @foreach ($view->tenant->trustBadges as $badge)
                <li class="badge">{{ $badge }}</li>
            @endforeach
        </ul>
    </section>
</x-layouts.shop>
