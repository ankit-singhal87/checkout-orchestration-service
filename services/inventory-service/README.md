# Inventory Service

Placeholder for the Go gRPC service that owns tenant-scoped stock reservation, materialization, release, and recovery.

## MVP Scope

- Document the boundary and contract shape for a Go inventory service.
- Treat [../../proto/inventory/v1/inventory.proto](../../proto/inventory/v1/inventory.proto) as a hand-authored contract.
- Do not commit generated code yet.
- Own inventory reservation and materialization outside Laravel.
- Keep local/dev behavior deterministic and tenant-scoped.
- Preserve the current Laravel simulator only as scaffold while the service boundary is introduced.
- Customer and shipment downstream processing is out of scope.

## Supersession Note

Earlier docs described inventory as a future extraction after the Laravel happy path. That remains useful implementation history, but it is not the target architecture. The target MVP boundary is:

- Laravel owns checkout FE/BFF, cart state, tenant validation, data collection, and fast order placement.
- Go inventory service owns reservation, materialization, release, idempotency, and inventory failure recovery.
- Go order preprocessor consumes `order.placed`, materializes the reservation, persists the durable order outcome, and emits `order.confirmed`.

## Service Contract Shape

- `Reserve(order_id, tenant_id, items)` reserves stock for the order placement snapshot and returns reservation status, reserved items, expiration time, and any rejected lines.
- `Materialize(order_id)` commits a live reservation into durable inventory movement during order confirmation.
- `Release(order_id)` releases a live reservation after cancellation, failure, or TTL cleanup.

`Reserve` must be idempotent for repeated calls with the same tenant, order, item snapshot, and idempotency context. Replays return the existing reservation result. Conflicting replays for the same `order_id` and tenant must fail closed instead of silently replacing the reservation.

`Materialize` and `Release` must also be idempotent. Materializing an already materialized reservation returns the committed result. Releasing an already released or expired reservation returns a terminal no-op result.

Reservations require a TTL. The exact duration is configurable, but the MVP contract assumes a short checkout-oriented hold, such as 10-15 minutes, with expiration handled as an operational cleanup rather than as proof that an order does or does not exist.

## Scaffold Baseline

The current local Laravel simulator is prior implementation scaffold:

- It reserves inventory synchronously before `orders` and `order.confirmed` are written.
- It locks tenant-owned product variants, fails closed for cross-tenant or missing variants, and decrements `stock_available` only when every requested quantity can be satisfied.
- Reservation outcomes are deterministic from tenant-scoped fixture stock, SKU, quantity, and idempotency key.
- Insufficient stock is a business outcome, not transport poison behavior.
- Invalid schema, missing tenant, unsafe payload, or impossible cross-tenant reference remains poison-message behavior.
