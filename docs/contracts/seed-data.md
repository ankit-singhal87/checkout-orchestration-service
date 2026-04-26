# Seed Data Contract

Seed data should make the demo feel realistic while staying deterministic and safe to rebuild.

## Tenants

- `fashion-store`
- `sports-outlet`

## Fixture Source

Seed source files live under `seed/fixtures` and are treated as deterministic inputs for Laravel seeders, tests, screenshots, and demos. Generated database rows, OpenSearch documents, Redis cache entries, and uploaded/static derivatives are rebuildable outputs.

Fixture IDs should be stable, human-readable, and tenant-prefixed where useful:

- Tenant IDs: `fashion-store`, `sports-outlet`
- Shop IDs: `fashion-main`, `sports-main`
- Product IDs: `fashion-product-001`
- Variant IDs: `fashion-variant-001-red-small`
- Cart IDs: `fashion-cart-happy-path`

## Catalog Shape

Each tenant should have:

- Categories.
- Products.
- Variants with tenant-appropriate options.
- Prices and optional discounts.
- Stock states.
- Product image metadata using safe placeholder or original assets.
- Trust badges such as delivery promise, return promise, sustainability, or authenticity.

## Minimum Fixture Counts

Phase 1 fixture targets:

- 2 tenants.
- 1 shop per tenant.
- 3 categories per tenant.
- 8-12 products per tenant for the first Blade UI.
- 2-4 variants per product.
- At least one in-stock, low-stock, and out-of-stock variant per tenant.
- At least one discounted item per tenant.
- At least one cart fixture per behavior scenario family.

## Checkout Scenarios

- Guest happy path.
- Logged-in customer path.
- High-value order.
- Out-of-stock item.
- Payment declined.
- Retry with same idempotency key.
- Concurrent order confirmation.
- Cross-tenant cart rejection.

## Determinism

Seeders should use fixed IDs or a fixed random seed so tests, screenshots, and demos are reproducible.

Rules:

- Faker must be seeded with a known scenario seed.
- Generated values must stay inside the tenant/test namespace.
- Fixture rebuilds must be idempotent: rerunning seeds updates or recreates the same logical records, not duplicates.
- Test-specific data must use a separate run namespace and clean itself up; shared demo fixtures should not be mutated by tests.
