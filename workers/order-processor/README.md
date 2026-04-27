# Order Processor Worker

Consumes committed order events for replay-safe post-order side effects and demo audit projections.

## Phase 3 Scope

- Consume committed order and simulator events from `checkout:events`.
- Own replay-safe order confirmation side effects and audit/demo projections.
- Keep payment and inventory simulator state behind their own processor boundaries unless the implementation lane explicitly combines them for a small local smoke path.
- Keep customer-facing order confirmation synchronous enough to commit the MySQL order record before returning to the shopper.

## Inputs

- Stream: `checkout:events`.
- Consumer group: `checkout.order-processor`.
- Initial event types: `order.confirmed`, `payment.capture.succeeded`, `payment.capture.failed`, and `order.confirmation.notification_requested`.
- Required envelope fields are defined in [domain-events.md](../../docs/agent/contracts/domain-events.md).

## Processor Contract

- Acknowledge only after the side effect/projection write commits.
- Dedupe by tenant, processor name, `eventId`, and business `idempotencyKey`.
- Propagate `correlationId`, `causationId`, `traceId`, and `requestId` on emitted events.
- Treat notification sending as a local stub in Phase 3. Do not add email/SMS provider credentials or external delivery.
- Treat audit projection as rebuildable from events.
- Send invalid or exhausted messages to the poison path documented in the domain-event contract.

## Local Consumer

Run the local consumer with `php artisan checkout:order-processor:consume`. It reads from `checkout:events` with consumer group `checkout.order-processor` and currently handles `order.confirmed` envelopes.

The local Docker runtime exposes the same consumer through `make up-order-processor`. Use `make test-order-processor-runtime` for the cheap command-registration smoke check.

The first local side effects are:

- `order_processor_processed_events` records the envelope and provides replay safety with unique tenant/processor/event and tenant/processor/idempotency constraints.
- `order_processor_audit_events` stores a rebuildable audit projection for consumed order events.
- `order_processor_poison_events` records invalid envelopes before the Redis Stream message is acknowledged.

## Consistency Rule

This worker must never decide whether an order exists. It reacts to committed outbox events after checkout/order writes complete.

## Incremental Tasks

1. Add the notification stub after duplicate delivery is proven safe.
2. Add an end-to-end event-pipeline smoke after the outbox publisher, consumer, and fixtures have one stable shared path.
