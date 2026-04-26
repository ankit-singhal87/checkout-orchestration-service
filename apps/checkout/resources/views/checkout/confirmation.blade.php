<x-layouts.shop
    title="Order confirmed"
    :tenant-name="$view->tenant->displayName"
    :tenant-color="$view->tenant->primaryColor"
>
    <section class="card panel stack">
        <p class="eyebrow">Order confirmed</p>
        <h1>Thanks, your order is in.</h1>
        <div class="row"><span>Reference</span><strong>{{ $view->orderRef }}</strong></div>
        <div class="row"><span>Status</span><strong>{{ $view->status }}</strong></div>
        <div class="row"><span>Total</span><strong>{{ $view->totalLabel }}</strong></div>
        <p>You can create an account after checkout later; this Phase 1 flow stays guest-first.</p>
        <p><a class="button secondary" href="{{ route('shop.index') }}">Back to shop</a></p>
    </section>
</x-layouts.shop>
