# Checkout MVP PRD

## Purpose

Build an independent multi-tenant checkout demo using original simplified
headless-commerce contracts and flows. The MVP proves a practical SaaS checkout
architecture with tenant isolation, seeded catalog and cart data, checkout
orchestration, consistency boundaries, observability contracts, and async
processing.

## Users

- Guest shoppers browse a tenant storefront, add seeded products to a cart, and
  complete checkout without mandatory login.
- Optional customers may sign in or create an account before, during, or after
  checkout.
- Tenant operators and platform reviewers evaluate tenant isolation, checkout
  reliability, local runtime behavior, and architecture tradeoffs.
- Project agents use the workflow artifacts to keep implementation scope narrow
  and validate work against accepted contracts.

## MVP Scope

- Laravel and Blade own the shopper-facing storefront, checkout UI, public API,
  tenant resolution, validation, view models, and first checkout orchestration
  path.
- MySQL is the durable source of truth for tenant, checkout, order, inventory,
  and identity data.
- Redis supports cache, session, idempotency, rate-limit, lock, and local stream
  use cases.
- Local/dev mode runs free or near-free with Docker Compose.
- Deploy mode remains optional and manually approved.
- Go is introduced only for selected processors or services where concurrency,
  async processing, or latency value is clear.
- Search remains a projection/read model and is never a transactional dependency
  for checkout.

## Out Of Scope

- Full checkout vendor parity, copied vendor APIs, proprietary UI behavior, and
  vendor-specific enterprise capabilities.
- Real payment provider integrations, shipment/carrier integrations, loyalty,
  collection points, full address-book breadth, fulfillment, and customer
  profile management.
- Production AWS deployment, `terraform apply`, registry publishing, or managed
  cloud services without explicit approval and cost guardrails.
- Splitting pricing, catalog, payment, order, and inventory into independent
  services before the Laravel happy path and service contracts justify it.

## Success Criteria

- Two demo tenants can complete a local checkout flow without login.
- Public API errors use RFC 9457 Problem Details with trace/request correlation.
- Tenant isolation has focused coverage and no public authorization path relies
  only on an untrusted tenant path segment or plain `X-Tenant-Id` header.
- Order confirmation is idempotent and backed by committed MySQL state before
  async side effects run.
- Local runtime, validation, and demo commands work without paid cloud services.
- Architecture decisions, design, contracts, and plans are discoverable under
  `docs/` workflow artifact directories.

## Acceptance Criteria

- AC-001: The repository contains workflow-native PRD, design, ADR, and work-plan
  artifacts under `docs/prd`, `docs/design`, `docs/adr`, and `docs/plans`.
- AC-002: The checkout app exposes tenant-aware storefront and checkout routes
  for product listing, product detail, cart, checkout, confirmation, and optional
  auth entry points.
- AC-003: Blade views receive explicit view models and do not query persistence
  directly.
- AC-004: Checkout/order writes use ACID MySQL transactions and idempotency for
  order confirmation.
- AC-005: Async side effects are published from committed outbox state and cannot
  decide whether an order exists.
- AC-006: Local mode can run through Docker Compose with Laravel, MySQL, Redis,
  optional search, optional observability, and optional RoadRunner/parity
  profiles.
- AC-007: Deploy mode remains documented as optional and requires manual
  approval, budget controls, TTL/ownership tags, rollback checkpoints, and
  destroy runbooks before cloud use.
- AC-008: Public tenant access is resolved from verified host/domain, signed
  token claims, or authenticated client configuration, not from an untrusted
  path segment alone.
- AC-009: Search projections are documented as eventually consistent read models
  and are not used for checkout transaction decisions.
- AC-010: Phase plans define what is implemented, active, deferred, and stopped
  so agents do not expand scope accidentally.

## References

- [Architecture design](../design/checkout-mvp-architecture-design.md)
- [Work plan](../plans/checkout-mvp-work-plan.md)
- [ADR index](../adr/README.md)
- [Phase 1 foundation plan](../plans/phase-1-foundation-plan.md)
- [Phase 2 system completion plan](../plans/phase-2-system-completion-plan.md)
- [Phase 3 boundary proof plan](../plans/phase-3-boundary-proof-plan.md)
