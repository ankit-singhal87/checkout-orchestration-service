<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Checkout Demo' }}</title>
        <style>
            body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 0; color: #111827; background: #f9fafb; }
            header { color: white; padding: 2rem; background: var(--tenant-color, #111827); }
            main { max-width: 960px; margin: 0 auto; padding: 2rem; }
            .grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
            .card { background: white; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1rem; }
            .badges { display: flex; flex-wrap: wrap; gap: .5rem; padding: 0; list-style: none; }
            .badge { background: #eef2ff; border-radius: 999px; padding: .25rem .65rem; font-size: .85rem; }
            a, button { color: var(--tenant-color, #111827); }
            button { cursor: pointer; }
        </style>
    </head>
    <body style="--tenant-color: {{ $tenantColor ?? '#111827' }}">
        <header>
            <h1>{{ $tenantName ?? 'Checkout Demo' }}</h1>
            <nav>
                <a href="{{ route('shop.index') }}">Shop</a>
                <span aria-hidden="true"> | </span>
                <a href="{{ route('cart.show') }}">Cart</a>
            </nav>
        </header>
        <main>
            {{ $slot }}
        </main>
    </body>
</html>
