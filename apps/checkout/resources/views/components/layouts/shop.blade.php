<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Checkout Demo' }}</title>
        <style>
            :root {
                color-scheme: light;
                --ink: #172026;
                --muted: #637083;
                --line: #d8dee8;
                --surface: #ffffff;
                --soft: #f4f7fb;
                --accent: var(--tenant-color, #14532d);
                --accent-dark: color-mix(in srgb, var(--accent) 78%, #111827);
            }

            * { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                color: var(--ink);
                background: var(--soft);
            }

            a { color: inherit; text-decoration: none; }
            button, input { font: inherit; }
            button { cursor: pointer; }

            .topbar {
                position: sticky;
                top: 0;
                z-index: 10;
                border-bottom: 1px solid color-mix(in srgb, var(--accent) 22%, var(--line));
                background: rgba(255, 255, 255, .94);
                backdrop-filter: blur(14px);
            }

            .topbar-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                max-width: 1180px;
                margin: 0 auto;
                padding: .9rem 1.25rem;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: .75rem;
                min-width: 0;
                font-weight: 800;
            }

            .brand-mark {
                width: 2.4rem;
                height: 2.4rem;
                border-radius: .55rem;
                background: var(--accent);
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .25);
            }

            .nav {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: .35rem;
            }

            .nav a {
                border: 1px solid transparent;
                border-radius: .45rem;
                padding: .55rem .75rem;
                color: var(--muted);
                font-size: .95rem;
                font-weight: 700;
            }

            .nav a:hover {
                border-color: var(--line);
                color: var(--ink);
                background: #fff;
            }

            main {
                max-width: 1180px;
                margin: 0 auto;
                padding: 1.5rem 1.25rem 3rem;
            }

            .page-head {
                display: grid;
                gap: .75rem;
                margin: 1rem 0 1.4rem;
            }

            .eyebrow {
                margin: 0;
                color: var(--accent-dark);
                font-size: .78rem;
                font-weight: 900;
                letter-spacing: 0;
                text-transform: uppercase;
            }

            h1, h2, h3, p { overflow-wrap: anywhere; }
            h1, h2, h3 { margin: 0; line-height: 1.12; }
            h1 { font-size: 3rem; max-width: 850px; }
            h2 { font-size: 1.65rem; }
            h3 { font-size: 1.05rem; }
            p { line-height: 1.55; }

            .lede {
                max-width: 720px;
                margin: 0;
                color: var(--muted);
                font-size: 1.08rem;
            }

            .grid {
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            }

            .card {
                background: var(--surface);
                border: 1px solid var(--line);
                border-radius: .5rem;
                box-shadow: 0 10px 30px rgba(23, 32, 38, .06);
            }

            .product-card {
                display: grid;
                overflow: hidden;
                min-height: 100%;
            }

            .product-body {
                display: grid;
                gap: .7rem;
                padding: 1rem;
            }

            .product-media {
                display: grid;
                place-items: center;
                min-height: 190px;
                border-bottom: 1px solid var(--line);
                background:
                    radial-gradient(circle at 28% 24%, rgba(255, 255, 255, .85), transparent 22%),
                    linear-gradient(135deg, color-mix(in srgb, var(--accent) 22%, #ffffff), #eaf0f7);
            }

            .product-shape {
                width: min(58%, 180px);
                aspect-ratio: 1;
                border-radius: 1.1rem;
                background: var(--accent);
                box-shadow: 0 18px 45px rgba(23, 32, 38, .16), inset 0 0 0 1px rgba(255, 255, 255, .3);
                transform: rotate(-6deg);
            }

            .product-shape[data-key*="jacket"] { border-radius: 36% 36% 18% 18%; }
            .product-shape[data-key*="dress"] { clip-path: polygon(50% 0, 78% 28%, 92% 100%, 8% 100%, 22% 28%); }
            .product-shape[data-key*="sneaker"] { aspect-ratio: 1.8; border-radius: 55% 18% 24% 45%; }
            .product-shape[data-key*="bag"], .product-shape[data-key*="pack"] { border-radius: 1.5rem .9rem 1.35rem .9rem; }
            .product-shape[data-key*="watch"] { width: min(42%, 120px); border-radius: 50%; }
            .product-shape[data-key*="yoga"] { aspect-ratio: 2.6; border-radius: 999px; }

            .price {
                margin: 0;
                font-size: 1.15rem;
                font-weight: 900;
            }

            .muted { color: var(--muted); }

            .badges {
                display: flex;
                flex-wrap: wrap;
                gap: .45rem;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .badge {
                border: 1px solid color-mix(in srgb, var(--accent) 20%, var(--line));
                border-radius: 999px;
                padding: .28rem .6rem;
                color: color-mix(in srgb, var(--accent) 62%, #172026);
                background: color-mix(in srgb, var(--accent) 8%, #fff);
                font-size: .78rem;
                font-weight: 800;
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 2.6rem;
                border: 1px solid var(--accent-dark);
                border-radius: .45rem;
                padding: .65rem .9rem;
                background: var(--accent);
                color: #fff;
                font-weight: 900;
            }

            .button.secondary {
                border-color: var(--line);
                background: #fff;
                color: var(--ink);
            }

            .button:disabled {
                border-color: #cbd5e1;
                background: #e5e7eb;
                color: #64748b;
                cursor: not-allowed;
            }

            .stack { display: grid; gap: 1rem; }
            .split { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr); align-items: start; }
            .panel { padding: 1rem; }
            .row { display: flex; justify-content: space-between; gap: 1rem; border-top: 1px solid var(--line); padding-top: .8rem; }

            label { display: grid; gap: .35rem; color: var(--muted); font-weight: 800; }
            input {
                width: 100%;
                min-height: 2.65rem;
                border: 1px solid var(--line);
                border-radius: .45rem;
                padding: .55rem .7rem;
                color: var(--ink);
                background: #fff;
            }

            .form-grid { display: grid; gap: .8rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .full { grid-column: 1 / -1; }
            .alert { border-color: #fecaca; background: #fff1f2; color: #991b1b; }

            @media (max-width: 760px) {
                .topbar-inner { align-items: flex-start; flex-direction: column; }
                .nav { justify-content: flex-start; }
                main { padding-inline: 1rem; }
                h1 { font-size: 2.1rem; }
                .split, .form-grid { grid-template-columns: 1fr; }
                .full { grid-column: auto; }
            }
        </style>
    </head>
    <body style="--tenant-color: {{ $tenantColor ?? '#14532d' }}">
        <header class="topbar">
            <div class="topbar-inner">
                <a class="brand" href="{{ route('shop.index') }}">
                    <span class="brand-mark" aria-hidden="true"></span>
                    <span>{{ $tenantName ?? 'Checkout Demo' }}</span>
                </a>
                <nav class="nav" aria-label="Primary">
                    <a href="{{ route('shop.index') }}">Shop</a>
                    <a href="{{ route('cart.show') }}">Cart</a>
                    <a href="{{ route('checkout.show') }}">Checkout</a>
                </nav>
            </div>
        </header>
        <main>
            {{ $slot }}
        </main>
    </body>
</html>
