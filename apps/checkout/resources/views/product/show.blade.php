<x-layouts.shop
    :title="$view->name"
    :tenant-name="$view->tenant->displayName"
    :tenant-color="$view->tenant->primaryColor"
>
    <article class="card">
        <p>{{ $view->imageAlt }}</p>
        <h2>{{ $view->name }}</h2>
        <p>{{ $view->description }}</p>
        <ul class="badges">
            @foreach ($view->badges as $badge)
                <li class="badge">{{ $badge }}</li>
            @endforeach
        </ul>

        <h3>Variants</h3>
        @foreach ($view->variants as $variant)
            <form method="post" action="{{ route('cart.items.store') }}">
                @csrf
                <input type="hidden" name="variant_id" value="{{ $variant['variantId'] }}">
                <p>
                    {{ $variant['label'] }} -
                    {{ $variant['priceLabel'] }} -
                    {{ $variant['stockState'] }}
                </p>
                <button type="submit" @disabled($variant['available'] < 1)>Add to cart</button>
            </form>
        @endforeach
    </article>
</x-layouts.shop>
