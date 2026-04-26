# ADR 0004: Checkout Consistency Model

## Status

Accepted

## Decision

Use strong ACID consistency for checkout/order writes and eventual consistency for search, analytics, email, metrics, and projections.

Production deploy mode uses Amazon RDS for MySQL for those ACID writes; local Docker Compose MySQL and future local Kubernetes MySQL bindings are development/test substitutes only.

## Consequences

- Order confirmation must be backed by a committed MySQL order record.
- Production database operations rely on RDS managed backups, patching, Multi-AZ/failover options, and restore workflows rather than self-managed MySQL inside EKS.
- Idempotency protects against duplicate orders.
- Inventory reservation belongs in the critical path.
- OpenSearch, notifications, and observability projections may catch up asynchronously.
