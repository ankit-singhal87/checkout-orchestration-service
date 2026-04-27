# ADR 0008: Checkout MVP Architecture Pivot

## Status

Accepted

## Context

The Phase 2 Laravel baseline proved the local storefront, checkout state, order confirmation, outbox, and Redis Streams path. The next MVP slice should now show a clearer service architecture instead of expanding Laravel-internal workers as the target design.

ADR 0006 still applies inside the Laravel app: controllers stay thin, Blade receives explicit view models, and domain rules stay out of templates. This ADR narrows Laravel's MVP role and promotes the first Go services where they have real ownership and runtime value.

## Decision

Reset the MVP target shape to:

- Laravel + Blade is the storefront, BFF, cart experience, and checkout data collector.
- Cart is ephemeral and session-owned. It may use Redis/session storage and signed checkout context, but it is not the durable order source of truth.
- Go Inventory Service owns stock availability, reservations, reservation materialization, reservation state, and reservation expiry.
- Go Order Preprocessor consumes `order.placed`, materializes the reservation, saves the order, and emits `order.confirmed`.
- Customer profile, shipment, notification, payment-provider depth, and fulfillment downstreams are out of scope for this MVP pivot.

Laravel still owns tenant resolution, storefront routes, Blade view models, form/API validation, checkout state collection, Problem Details rendering, and publishing `order.placed` through the outbox. It should not keep growing Laravel-internal checkout workers as the target architecture. Existing Laravel worker commands remain temporary scaffolding for local demos, contract proving, and migration safety until the Go consumers replace them.

Use Go now because the new boundaries are real service boundaries: inventory reservation is concurrency-sensitive, order preprocessing is a message-consumer workflow, and both benefit from explicit long-running process ownership, idempotent MQ handling, and bounded contracts.

## Failure Recovery Model

- Orders use explicit states such as `placed`, `preprocessing`, `confirmed`, `failed`, and `manual_review`.
- Reservations use explicit states such as `requested`, `held`, `materialized`, `released`, `expired`, and `failed`.
- Reservation holds have TTLs. Expired holds are releasable and must not be materialized into confirmed orders.
- `order_id` is the idempotency key across Laravel, Inventory, and Order Preprocessor processing.
- Consumers acknowledge MQ messages only after the reservation/order state transition and any required outbox writes commit successfully.
- Outbox publishing is the reliable event boundary for `order.placed`, `order.confirmed`, and compensating/recovery events.
- Retryable failures stay retryable with bounded backoff. Poison messages move to a DLQ or manual-review path with tenant, order, event, trace, and failure metadata.
- Customer-facing checkout must not depend on downstream customer, shipment, notification, analytics, or search projections.

## Tenant Data Plane

Tenant identity is resolved from verified host, signed checkout token, or authenticated internal client context. The durable tenant registry lives in a relational control plane and stores tenant, shop, verified domain, feature, and data-plane routing records.

Redis may cache tenant lookup results, short-lived locks, idempotency markers, and session/cart data. Secrets are stored by reference, not copied into tenant records or events. Each tenant has stable logical database endpoints in configuration; those endpoints may map to shared databases/schemas for the MVP or dedicated databases later without changing public tenant identity.

## Identity Model

Keycloak is the identity provider. Customer profile is tenant-scoped business data, not the global identity record.

The same human can have separate customer accounts per tenant. A guest profile created during checkout can later link to a Keycloak subject for that tenant. Linking does not merge customer data across tenants unless a future explicit cross-tenant account model is designed and accepted.

## Consequences

- ADR 0002 is refined: Laravel remains first for the storefront/BFF, but Go is no longer postponed for inventory and order preprocessing.
- ADR 0004 is refined: the durable order is confirmed only after Go preprocessing materializes a valid reservation and commits the order outcome.
- Phase 3 work should prioritize contracts, events, and runtime safety for Inventory Service and Order Preprocessor over additional Laravel-internal worker expansion.
- The MVP becomes a portfolio-friendly demonstration of Laravel FE/BFF plus Go services, without expanding into customer, shipment, fulfillment, or production cloud deployment.
