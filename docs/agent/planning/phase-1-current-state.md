# Phase 1 current state (snapshot)

This document records what is implemented in the repository at the time of the last foundation push toward Phase 1. Update it when major slices land or the scope of Phase 1 changes.

**Last updated:** 2026-04-26

## Summary

- **Laravel app** at [apps/checkout](../../../apps/checkout) is the first application boundary: host-resolved multi-tenancy, expanded deterministic catalog fixtures, cart, **guest web checkout** through confirmation, a first **public checkout API** for config/state/address/shipping/payment/order confirmation, **idempotent order confirmation**, and a **durable outbox event** on successful confirmation.
- **Local runtime:** Docker Compose starts MySQL/Redis by default, the checkout app behind `COMPOSE_PROFILES=app`, and optional search/observability/identity profiles. The checkout container runs pending migrations and idempotent seeders on local startup.
- **Observability baseline:** Laravel adds request/trace correlation headers, propagates trace IDs into Problem Details, and emits structured HTTP completion logs to JSON stderr in local containers. Full OTLP metrics/traces and exporter selection remain later work.
- **Testing and guardrails:** Pest feature tests with real MySQL in CI; parallel execution via `php artisan test --parallel` in [scripts/test/checkout-app.sh](../../../scripts/test/checkout-app.sh); migration immutability is enforced by shared pre-push/CI scripts.
- **Standards:** PHP 8.5-oriented standards in [docs/agent/coding-standards/php-8.5.md](../coding-standards/php-8.5.md); clean boundaries in [docs/human/adr/0006-laravel-clean-boundaries.md](../../human/adr/0006-laravel-clean-boundaries.md).
- **Not yet in scope** for this snapshot: full SCAYLE-shaped checkout REST breadth, RoadRunner runtime wiring, Go workers, OpenSearch indexing/read model wiring, full OpenTelemetry metrics/traces/export, and AWS deploy assets.

## Laravel surface

All tenant-scoped routes use the `tenant` middleware (host → `TenantContext`).

| Area | Status |
| --- | --- |
| Shop listing, product detail | Done |
| Cart add/show | Done |
| Guest checkout: state, address, shipping option, payment method, confirm, confirmation page | Done |
| Public checkout API: config, state create/read, address, shipping option, payment method, order confirmation | First slice wired in `routes/api.php` with tenant middleware, Problem Details, and Pest coverage |
| Stubs for extended API and web routes | Present under `*.stub` for contract validation; not all full SCAYLE-shaped API endpoints are wired yet |

**Named web routes of note:** `shop.index`, `shop.product.show`, `cart.show`, `cart.items.store`, `checkout.show`, `checkout.address.update`, `checkout.shipping-option.update`, `checkout.payment-method.update`, `checkout.confirm`, `checkout.confirmation.show` (see [apps/checkout/routes/web.php](../../../apps/checkout/routes/web.php)).

**Named API routes of note:** `api.checkout.config.show`, `api.checkout.state.put`, `api.checkout.state.show`, `api.checkout.address.put`, `api.checkout.shipping-option.put`, `api.checkout.payment-method.put`, `api.checkout.order-confirmation.store` (see [apps/checkout/routes/api.php](../../../apps/checkout/routes/api.php)).

## Persistence (MySQL, MVP slice)

- **Existing:** `tenants`, `products`, `product_variants`, `carts`, `cart_items`.
- **Guest checkout / order:** `checkout_states`, `orders`, `outbox_events`. A forward repair migration exists so older local databases created before the full checkout/order tables landed can catch up without `migrate:fresh`.

## Behavior invariants (implemented in code)

- **Tenant isolation:** Resolution by HTTP host; cross-tenant cart/API state access returns RFC 9457-style problem JSON where implemented.
- **Idempotent order confirmation:** Web flow uses a session-backed idempotency key; API flow accepts an explicit idempotency key. The same key yields the same order and redirect/response target (covered in Pest).
- **Problem Details:** API validation, missing checkout state, tenant access denial, and checkout conflicts return `application/problem+json` where implemented.
- **HTTP observability:** Handled HTTP responses include `X-Request-Id` and `X-Trace-Id`; structured completion logs include request ID, trace ID, route, status, latency, and safe tenant/shop slugs.
- **Outbox:** On successful confirmation, an `outbox_events` row is written for `order.confirmed` (publication to Redis/SQS is a later step).
- **Seed data:** Two demo tenants have enough deterministic product fixtures to make the storefront and checkout flow inspectable locally.

## CI and validation

- **GitLab:** `checkout:tests` runs Composer + [scripts/test/checkout-app.sh](../../../scripts/test/checkout-app.sh) with MySQL/Redis service containers.
- **Local:** `sh scripts/test/checkout-app.sh`, `sh scripts/ci/validate-phase1.sh`, and `sh scripts/git/pre-push.sh`.

## Documentation and contracts (living docs)

- **Plan:** [docs/human/planning/checkout-mvp-plan.md](../../human/planning/checkout-mvp-plan.md) (source of truth for phase scope).
- **BDD feature files:** [apps/checkout/tests/Behavior/features](../../../apps/checkout/tests/Behavior/features) (scenarios; executable automation can grow with Gauge or Pest mappings later).
- **Contracts:** [docs/agent/contracts](../contracts) (checkout state, problem details, tenant model, BDD/TDD, seed data, etc.).

## Later-Phase Follow-ups

- Extend the public checkout API beyond the first slice: basket item updates, vouchers, address copy/delete, collection points, loyalty, and address book remain unwired.
- **Problem Details** for remaining HTML vs JSON checkout validation paths; align field paths with [docs/agent/contracts/problem-details.md](../contracts/problem-details.md).
- **Outbox consumer** (Laravel job or small worker) to mark `published_at` and optional Redis Stream publish.
- OpenSearch indexing/read-model projection.
- RoadRunner production-style runtime wiring.
- OpenTelemetry metrics/traces and exporter profile selection.

## How to re-verify locally

```bash
sh scripts/test/checkout-app.sh
sh scripts/ci/validate-phase1.sh
MIGRATION_IMMUTABILITY_BASE_REF=origin/cursor/phase-1-foundation sh scripts/git/pre-push.sh
```

When changing persistence or routes, run the same commands before pushing.
