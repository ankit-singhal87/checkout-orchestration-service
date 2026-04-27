# Order Processor Worker

Placeholder for async post-order side effects such as payment settlement simulation, confirmation notifications, search indexing, analytics events, and demo audit projections.

## Phase 3 Scope

- Consume committed order and simulator events from `checkout:events`.
- Own replay-safe order confirmation side effects, notification stubs, and audit/demo projections.
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

Run the first local consumer with `php artisan checkout:order-processor:consume`. It reads from `checkout:events` with consumer group `checkout.order-processor` and currently handles `order.confirmed` envelopes.

The first local side effect is the processed-event ledger in `order_processor_processed_events`. It records the envelope and provides replay safety with unique tenant/processor/event and tenant/processor/idempotency constraints. Invalid envelopes are recorded in `order_processor_poison_events` before the Redis Stream message is acknowledged.

## Consistency Rule

This worker must never decide whether an order exists. It reacts to committed outbox events after checkout/order writes complete.

## Incremental Tasks

1. Add the notification stub after duplicate delivery is proven safe.
2. Add the audit/demo projection after poison handling is visible in the runbook.
