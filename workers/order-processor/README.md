# Order Preprocessor Worker

Placeholder for the MVP Go worker that turns an order placement into a confirmed order.

## MVP Scope

- Consume `order.placed`.
- Call inventory `Materialize(order_id)` for the placement reservation.
- Save the durable MySQL order outcome using the placement snapshot.
- Emit `order.confirmed` from the committed outbox transaction.
- Leave customer, shipment, email, analytics, and search side effects to downstream consumers.

## Supersession Note

The existing Laravel order-processor consumer is current scaffold and prior implementation baseline. It consumes `order.confirmed` for replay-safe audit/demo projections and poison-message tests. It is not the target owner of order confirmation.

The target architecture separates order placement from order confirmation:

- Laravel places the order quickly and emits `order.placed`.
- The Go order preprocessor owns the order confirmation boundary.
- The Go inventory service owns reservation materialization.
- `order.confirmed` means inventory materialization and durable order outcome persistence completed.

## Inputs

- Stream: `checkout:events`.
- Consumer group: `checkout.order-processor`.
- Target event type: `order.placed`.
- Scaffold event types: `order.confirmed`, `payment.capture.succeeded`, `payment.capture.failed`, and `order.confirmation.notification_requested`.
- Required envelope fields are defined in [domain-events.md](../../docs/agent/contracts/domain-events.md).

## Processor Contract

- Acknowledge only after the side effect/projection write commits.
- Dedupe by tenant, processor name, `eventId`, and business `idempotencyKey`.
- Propagate `correlationId`, `causationId`, `traceId`, and `requestId` on emitted events.
- Treat notification sending as a downstream local stub, not as part of order confirmation.
- Treat audit projection as rebuildable from events.
- Send invalid or exhausted messages to the poison path documented in the domain-event contract.

## Target Consistency Rule

This worker owns the Order Confirmation boundary, not the Order Placement boundary. It reacts to committed placement events and must be idempotent by `tenant_id`, `order_id`, and `idempotency_key`.

If inventory materialization succeeds, the worker saves the durable order outcome and emits `order.confirmed`. If inventory materialization fails, the worker records a retryable or terminal confirmation failure according to the checkout state contract. Async downstream side effects must never decide whether an order exists.

The worker must not perform customer, shipment, email, analytics, or search side effects directly. Those are downstream consumers of `order.confirmed` and remain out of scope for this MVP contract.

## Local Scaffold Runtime

Run the current Laravel scaffold consumer with `php artisan checkout:order-processor:consume`. It reads from `checkout:events` with consumer group `checkout.order-processor` and currently handles `order.confirmed` envelopes.

The local Docker runtime exposes the same consumer through `make up-order-processor`. Use `make test-order-processor-runtime` for the cheap command-registration smoke check.

The current scaffold side effects are:

- `order_processor_processed_events` records the envelope and provides replay safety with unique tenant/processor/event and tenant/processor/idempotency constraints.
- `order_processor_audit_events` stores a rebuildable audit projection for consumed order events.
- `order_processor_poison_events` records invalid envelopes before the Redis Stream message is acknowledged.
