# Proto Contracts

Shared contracts live here when a boundary is intentionally prepared outside the Laravel checkout app.

## MVP Scope

- Keep this directory as a contract placeholder for the Go inventory service boundary.
- Contracts are hand-authored for review and boundary discussion.
- Do not generate proto code until the Go service implementation lane is approved.
- Generation tooling, package layout, and CI checks must be approved before generated artifacts are added.
- Prefer documenting expected request/response concepts in [docs/contracts](../docs/contracts) before adding `.proto` files.
- Use the Phase 3 event contracts and worker READMEs as the first boundary definition for inventory, order processing, audit, and projection behavior.
- Customer, shipment, email, analytics, and search consumers are downstream of order confirmation and out of scope for this scaffold.

## Current Contracts

The first gRPC boundary is inventory reservation and materialization:

- `Reserve(order_id, tenant_id, items)` creates or refreshes a tenant-scoped reservation for an order placement.
- `Materialize(order_id)` converts a valid reservation into committed inventory movement during order confirmation.
- `Release(order_id)` releases an unused or failed reservation.

All commands must be idempotent for the same `tenant_id`, `order_id`, item snapshot, and idempotency context. Reservations require a TTL so abandoned order placements do not hold stock forever.

- [inventory/v1/inventory.proto](inventory/v1/inventory.proto) defines the tentative inventory reservation, materialization, and release service boundary.
- [order/v1/order_events.proto](order/v1/order_events.proto) defines the tentative order placed and order confirmed event payloads.

Event envelopes in [domain-events.md](../docs/contracts/domain-events.md) remain the stable integration contract until generated tooling is approved.

## Supersession Note

The earlier Laravel/local worker contract is current scaffold and prior implementation baseline. It is superseded as the target architecture by the MVP service boundary: Laravel places orders and publishes `order.placed`; the Go inventory service owns reservation/materialization; the Go order preprocessor confirms orders and publishes `order.confirmed`.
