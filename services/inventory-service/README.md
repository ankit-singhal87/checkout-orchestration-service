# Inventory Service

Placeholder for a future Go gRPC service that may own stock reservation and release after the Laravel happy path is working.

## Phase 1 Scope

- Document the likely boundary and contract shape.
- Avoid implementation until there is a measured concurrency, async, or latency reason to extract from Laravel.

## Initial Boundary

Laravel remains the source of checkout orchestration. If this service is introduced later, it should expose tenant-scoped reservation commands and deterministic release behavior, while MySQL-backed checkout/order state remains the customer-facing source of truth.
