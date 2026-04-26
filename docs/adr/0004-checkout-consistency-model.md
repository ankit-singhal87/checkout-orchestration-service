# ADR 0004: Checkout Consistency Model

## Status

Accepted

## Decision

Use strong ACID consistency for checkout/order writes and eventual consistency for search, analytics, email, metrics, and projections.

## Consequences

- Order confirmation must be backed by a committed MySQL order record.
- Idempotency protects against duplicate orders.
- Inventory reservation belongs in the critical path.
- OpenSearch, notifications, and observability projections may catch up asynchronously.
