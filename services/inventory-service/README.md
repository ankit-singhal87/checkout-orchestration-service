# Inventory Service

Placeholder for a future Go gRPC service that may own stock reservation and release after the Laravel happy path is working.

## Phase 3 Scope

- Document the likely boundary and contract shape.
- Avoid implementation until there is a measured concurrency, async, or latency reason to extract from Laravel.
- Start as a deterministic local processor or Laravel-owned module before any separate service runtime.

## Initial Boundary

Laravel remains the source of checkout orchestration. If this service is introduced later, it should expose tenant-scoped reservation commands and deterministic release behavior, while MySQL-backed checkout/order state remains the customer-facing source of truth.

## Simulator Contract

- Input events: `order.confirmed` or `inventory.reservation.requested`.
- Consumer group: `checkout.inventory-reservations`.
- Output events: `inventory.reservation.succeeded` or `inventory.reservation.failed`.
- Reservation idempotency key: tenant, order id, SKU, quantity, and order confirmation idempotency key.
- Reservation outcomes must be deterministic from tenant-scoped fixture stock, SKU, quantity, and idempotency key.
- Failures such as insufficient stock are business outcomes, not transport poison messages.
- Invalid schema, missing tenant, unsafe payload, or impossible cross-tenant reference is poison-message behavior.

## Extraction Guardrail

Keep inventory in Laravel or a local worker until evidence shows a separate runtime is useful. Evidence may include lock contention, throughput pressure, or latency that cannot be addressed cleanly inside the Laravel worker model. Do not add `.proto` files or generated clients before that review.
