# Order Processor Worker

Placeholder for async post-order side effects such as payment settlement simulation, confirmation notifications, search indexing, analytics events, and demo audit projections.

## Phase 1 Scope

- Document event inputs and side-effect ownership.
- Keep customer-facing order confirmation synchronous enough to commit the MySQL order record before returning to the shopper.

## Consistency Rule

This worker must never decide whether an order exists. It reacts to committed outbox events after checkout/order writes complete.
