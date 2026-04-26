<x-layouts.shop
    :title="$view->tenant->displayName.' Shop'"
    :tenant-name="$view->tenant->displayName"
    :tenant-color="$view->tenant->primaryColor"
>
    <section class="page-head">
        <p class="eyebrow">Tenant storefront</p>
        <h1>{{ $view->tenant->displayName }}</h1>
        <p class="lede">Browse deterministic demo products, add an in-stock variant, and continue through the guest checkout flow.</p>
        <ul class="badges" aria-label="Tenant trust badges">
            @foreach ($view->tenant->trustBadges as $badge)
                <li class="badge">{{ $badge }}</li>
            @endforeach
        </ul>
    </section>

    <section class="grid" aria-label="Products">
        @foreach ($view->products as $product)
            <article class="card product-card">
                <div class="product-media" role="img" aria-label="{{ $product->imageAlt }}">
                    <span class="product-shape" data-key="{{ $product->imageKey }}" aria-hidden="true"></span>
                </div>
                <div class="product-body">
                    <h3>{{ $product->name }}</h3>
                    <p class="muted">{{ $product->description }}</p>
                    <p class="price">{{ $product->priceLabel }}</p>
                    <ul class="badges">
                        @foreach ($product->badges as $badge)
                            <li class="badge">{{ $badge }}</li>
                        @endforeach
                    </ul>
                    <a class="button secondary" href="{{ route('shop.product.show', ['slug' => $product->slug]) }}">View product</a>
                </div>
            </article>
        @endforeach
    </section>
</x-layouts.shop>
