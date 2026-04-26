# Seed Data

Deterministic demo data for tenants, catalog, products, carts, checkout scenarios, inventory, and optional identity fixtures.

## Phase 1 Scope

- Define seed data shape before generating large fixtures.
- Start with two tenants: `fashion-store` and `sports-outlet`.
- Keep data realistic enough for checkout demos without using copyrighted brand assets.
- Keep source fixtures under [seed/fixtures](fixtures).
- Treat MySQL, Redis, and OpenSearch state as rebuildable outputs.

## Reset Rule

Seed data should be rebuildable from source files. Local database/search/cache state can be deleted by intentionally removing Docker volumes.

## Fixture Rules

- Use stable fixture IDs for shared demo data.
- Use Faker only with explicit seeds.
- Keep test-generated data in a unique run/test namespace.
- Make seed reruns idempotent so duplicate tenants, products, carts, or checkout scenarios are not created.
