# Foundation and Closed Phase 2 Current State

This document records what is implemented in the repository after the Phase 1 foundation and closed Phase 2 local checkout/system-completion work. Update it when major slices land or the active phase scope changes.

**Last updated:** 2026-04-27

## Summary

- **Laravel app** at [apps/checkout](../../../apps/checkout) is the first application boundary: host-resolved multi-tenancy, expanded deterministic catalog fixtures, cart, **guest web checkout** through confirmation, public checkout API config/state/address/basket item/shipping/payment/order confirmation, **idempotent order confirmation**, and a **durable outbox event** on successful confirmation.
- **Local runtime:** Docker Compose starts MySQL/Redis by default, the checkout app behind `COMPOSE_PROFILES=app`, and optional search/observability/identity profiles. The checkout container runs pending migrations and idempotent seeders, then starts PHP-FPM behind Nginx over HTTP/1.1 for the default local path. `make up-roadrunner` starts the optional RoadRunner/Octane performance profile, and `make up-parity` adds a Caddy HTTPS/H1/H2/H3 edge path.
- **Observability baseline:** Laravel adds request/trace correlation headers, propagates trace IDs into Problem Details, and emits structured HTTP completion logs to JSON stderr in local containers. Full OTLP metrics/traces and exporter selection remain later work.
- **Testing and guardrails:** Pest feature tests with real MySQL in CI; parallel execution via `php artisan test --parallel` in [scripts/test/checkout-app.sh](../../../scripts/test/checkout-app.sh); migration immutability and Markdown link hygiene are enforced by shared validation scripts. Common local commands are aggregated in [Makefile](../../../Makefile).
- **Standards:** PHP 8.5-oriented standards in [docs/agent/coding-standards/php-8.5.md](../coding-standards/php-8.5.md); clean boundaries in [docs/human/adr/0006-laravel-clean-boundaries.md](../../human/adr/0006-laravel-clean-boundaries.md).
- **Not yet wired** for this snapshot: vouchers, address copy/delete, collection points, loyalty, address book, first dedicated worker runtime, OpenSearch indexing/read model wiring, full OpenTelemetry metrics/traces/export, and AWS deploy assets. These are Phase 3 or Phase 4+ concerns depending on the active plan.

## Laravel surface

All tenant-scoped routes use the `tenant` middleware (host → `TenantContext`).

| Area | Status |
| --- | --- |
| Shop listing, product detail | Done |
| Cart add/show | Done |
| Guest checkout: state, address, shipping option, payment method, confirm, confirmation page | Done |
| Public checkout API: config, state create/read, address, basket item quantity, shipping option, payment method, order confirmation | Phase 2 breadth has started in `routes/api.php` with tenant middleware, Problem Details, and Pest coverage |
| Stubs for extended API and web routes | Present under `*.stub` for contract validation; not all checkout API breadth endpoints are wired yet |

**Named web routes of note:** `shop.index`, `shop.product.show`, `cart.show`, `cart.items.store`, `checkout.show`, `checkout.address.update`, `checkout.shipping-option.update`, `checkout.payment-method.update`, `checkout.confirm`, `checkout.confirmation.show` (see [apps/checkout/routes/web.php](../../../apps/checkout/routes/web.php)).

**Named API routes of note:** `api.checkout.config.show`, `api.checkout.state.put`, `api.checkout.state.show`, `api.checkout.address.put`, `api.checkout.basket-item.put`, `api.checkout.shipping-option.put`, `api.checkout.payment-method.put`, `api.checkout.order-confirmation.store` (see [apps/checkout/routes/api.php](../../../apps/checkout/routes/api.php)).

## Persistence (MySQL, MVP slice)

- **Existing:** `tenants`, `products`, `product_variants`, `carts`, `cart_items`.
- **Guest checkout / order:** `checkout_states`, `orders`, `outbox_events`. A forward repair migration exists so older local databases created before the full checkout/order tables landed can catch up without `migrate:fresh`.

## Behavior invariants (implemented in code)

- **Tenant isolation:** Resolution by HTTP host; cross-tenant cart/API state access returns RFC 9457-style problem JSON where implemented.
- **Idempotent order confirmation:** Web flow uses a session-backed idempotency key; API flow accepts an explicit idempotency key. The same key yields the same order and redirect/response target (covered in Pest).
- **Problem Details:** API validation, missing checkout state, tenant access denial, checkout conflicts, and JSON clients hitting checkout web mutation paths return `application/problem+json` where implemented.
- **Basket updates:** API clients can update or remove checkout basket items by variant ID; basket changes recalculate totals and clear shipping/payment selections.
- **HTTP observability:** Handled HTTP responses include `X-Request-Id` and `X-Trace-Id`; structured completion logs include request ID, trace ID, route, status, latency, and safe tenant/shop slugs.
- **Outbox:** On successful confirmation, an `outbox_events` row is written for `order.confirmed`; `checkout:outbox:publish` publishes unpublished rows to Redis Streams and marks them published after success.
- **Seed data:** Two demo tenants have enough deterministic product fixtures to make the storefront and checkout flow inspectable locally.

## CI and validation

- **GitLab:** `checkout:tests` runs Composer + [scripts/test/checkout-app.sh](../../../scripts/test/checkout-app.sh) with MySQL/Redis service containers.
- **Local:** `make test-checkout`, `make test-markdown-links`, `make validate-phase1`, `make pre-push`, and `make pre-push-full`.

## Documentation and contracts (living docs)

- **Plan:** [docs/human/planning/checkout-mvp-plan.md](../../human/planning/checkout-mvp-plan.md) (source of truth for phase scope).
- **BDD feature files:** [apps/checkout/tests/Behavior/features](../../../apps/checkout/tests/Behavior/features) (scenarios; executable automation can grow with Gauge or Pest mappings later).
- **Contracts:** [docs/agent/contracts](../contracts) (checkout state, problem details, tenant model, BDD/TDD, seed data, etc.).

## Later-Phase Follow-ups

- OpenSearch indexing/read-model projection.
- RoadRunner/Octane long-running worker safety and production-style runtime hardening beyond the optional local performance profile.
- OpenTelemetry metrics/traces and exporter profile selection.

See [phase-3-peripheral-services.md](phase-3-peripheral-services.md) for the active Phase 3 priority order. [phase-2-system-completion.md](phase-2-system-completion.md) is now the closed Phase 2 baseline.

## How to re-verify locally

```bash
make test-checkout
make validate-phase1
MIGRATION_IMMUTABILITY_BASE_REF=origin/cursor/phase-1-foundation make pre-push
```

When changing persistence or routes, run the same commands before pushing.
