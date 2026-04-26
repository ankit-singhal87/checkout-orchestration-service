# Phase 1 Foundation

Phase 1 turns the Phase 0 plan into a repo foundation without overbuilding services.

## Parallel Work Streams

- Atlas and Quill: architecture contracts, tenant model, checkout state, API shape, and Problem Details.
- Loom: Laravel app skeleton and folder conventions under `apps/checkout`.
- Forge: monorepo layout, Docker image placeholders, CI script structure, and local runtime boundaries.
- Sprout: seed data model and deterministic fixture plan.
- Beacon and Gauge: observability/test strategy alignment.
- Shield: tenant isolation, secret handling, and optional Keycloak review.
- Hammer: future Go boundary notes only; no service extraction yet.

## Acceptance Criteria

- Monorepo directories exist with ownership docs.
- Checkout app can be bootstrapped through Docker without host PHP or Composer.
- Initial checkout API contract exists.
- Laravel folder conventions, route surface, testing strategy, and view model shapes are documented.
- BDD/TDD workflow and test folder conventions are documented.
- Initial behavior specs exist for guest checkout, tenant browsing, checkout state, idempotency, and tenant isolation.
- Tenant model, checkout state, Problem Details, domain events, and seed data contracts exist.
- Latency SLOs are documented as engineering targets.
- Laravel remains the first implementation target.
- Go service directories remain placeholders until extraction is justified.
