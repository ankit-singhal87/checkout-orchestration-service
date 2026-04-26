<x-layouts.shop
    :title="$view->tenant->displayName.' Shop'"
    :tenant-name="$view->tenant->displayName"
    :tenant-color="$view->tenant->primaryColor"
>
    <section>
        <h2>Products</h2>
        <ul class="badges" aria-label="Tenant trust badges">
            @foreach ($view->tenant->trustBadges as $badge)
                <li class="badge">{{ $badge }}</li>
            @endforeach
        </ul>

        <div class="grid">
            @foreach ($view->products as $product)
                <article class="card">
                    <p>{{ $product->imageAlt }}</p>
                    <h3>{{ $product->name }}</h3>
                    <p>{{ $product->description }}</p>
                    <p><strong>{{ $product->priceLabel }}</strong></p>
                    <ul class="badges">
                        @foreach ($product->badges as $badge)
                            <li class="badge">{{ $badge }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('shop.product.show', ['slug' => $product->slug]) }}">View product</a>
                </article>
            @endforeach
        </div>
    </section>
</x-layouts.shop>
