<x-layouts.shop
    :title="$view->name"
    :tenant-name="$view->tenant->displayName"
    :tenant-color="$view->tenant->primaryColor"
>
    <article class="split">
        <div class="card product-media" role="img" aria-label="{{ $view->imageAlt }}">
            <span class="product-shape" data-key="{{ $view->imageKey }}" aria-hidden="true"></span>
        </div>

        <div class="card panel stack">
            <div class="stack">
                <p class="eyebrow">Product detail</p>
                <h1>{{ $view->name }}</h1>
                <p class="lede">{{ $view->description }}</p>
                <ul class="badges">
                    @foreach ($view->badges as $badge)
                        <li class="badge">{{ $badge }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="stack">
                <h2>Choose a variant</h2>
                @foreach ($view->variants as $variant)
                    <form class="card panel row" method="post" action="{{ route('cart.items.store') }}">
                        @csrf
                        <input type="hidden" name="variant_id" value="{{ $variant['variantId'] }}">
                        <span>
                            <strong>{{ $variant['label'] }}</strong><br>
                            <span class="muted">{{ $variant['stockState'] }} &middot; {{ $variant['priceLabel'] }}</span>
                        </span>
                        <button class="button" type="submit" @disabled($variant['available'] < 1)>Add</button>
                    </form>
                @endforeach
            </div>
        </div>
    </article>
</x-layouts.shop>
