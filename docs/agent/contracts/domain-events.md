# Domain Events

Domain events record committed business facts and feed local Redis Streams or deploy-mode messaging.

## MVP Events

- `checkout.state.created`
- `checkout.address.updated`
- `checkout.shipping_option.selected`
- `checkout.payment_method.selected`
- `checkout.order.confirmation_requested`
- `order.placed`
- `order.confirmed`

## Phase 3 Worker Events

- `inventory.reservation.requested`
- `inventory.reservation.succeeded`
- `inventory.reservation.failed`
- `payment.authorization.requested`
- `payment.authorization.succeeded`
- `payment.authorization.failed`
- `payment.capture.requested`
- `payment.capture.succeeded`
- `payment.capture.failed`
- `order.confirmation.notification_requested`
- `audit.event.recorded`

## Later Events

- `search.indexing.requested`

## Envelope

```json
{
  "eventId": "01HV...",
  "schemaVersion": 1,
  "eventType": "order.confirmed",
  "occurredAt": "2026-04-26T00:00:00Z",
  "tenantId": "fashion-store",
  "aggregateType": "order",
  "aggregateId": "ord_...",
  "correlationId": "req_...",
  "causationId": "01HV...",
  "idempotencyKey": "tenant:event:business-key",
  "traceId": "01HV...",
  "requestId": "req_...",
  "payload": {}
}
```

Required fields:

- `eventId`: globally unique ULID/UUID generated when the outbox row is created.
- `schemaVersion`: positive integer for the payload schema of `eventType`.
- `eventType`: stable lowercase dotted name.
- `occurredAt`: UTC timestamp for the committed business fact.
- `tenantId`: public tenant slug used for routing and observability only; handlers must resolve trusted tenant records before writes.
- `aggregateType` and `aggregateId`: entity stream key used for ordering and idempotency scopes.
- `correlationId`: request or workflow id shared across related events.
- `causationId`: triggering event id when this event was produced by a processor; use `null` or omit for request-originated events.
- `idempotencyKey`: deterministic key for handler side effects when a natural business key exists.
- `traceId` and `requestId`: propagated from the public request or worker context when available.
- `payload`: schema-versioned event body. Payloads must not contain secrets, raw payment data, or cross-tenant identifiers.

Local Redis Streams map the envelope fields to message fields on stream `checkout:events`. Payload remains JSON encoded. Deploy-mode transports should keep the same envelope and only change the transport adapter.

For checkout-created `order.placed` rows, the outbox payload must include `correlationId`, `causationId`, and `idempotencyKey` so the outbox publisher can promote them into Redis Stream envelope fields. `correlationId` is the public checkout id, `causationId` is `checkout.placement:{checkoutId}`, and `idempotencyKey` is `tenantId:order.placed:{checkoutPlacementIdempotencyKey}`.

For order-preprocessor-created `order.confirmed` rows, the outbox payload must include `correlationId`, `causationId`, and `idempotencyKey`. `correlationId` is the original placement workflow id, `causationId` is the consumed `order.placed` event id, and `idempotencyKey` is `tenantId:order.confirmed:{orderPlacementIdempotencyKey}`.

## Order Event Payloads

`order.placed` records that Laravel checkout accepted an order placement request and committed the placement intent. It does not mean inventory has been materialized or that customer, shipment, notification, analytics, or search work is complete.

Required payload fields:

- `event_id`
- `tenant_id`
- `order_id`
- `idempotency_key`
- `items`, including SKU/product reference, quantity, unit price, and line total
- `totals`, including subtotal, discounts, tax, shipping, grand total, and `currency`
- `occurred_at`
- `correlation_id`
- `causation_id`

`order.confirmed` records that the Go order preprocessor completed inventory materialization through the Go inventory service and saved the durable order outcome.

Required payload fields:

- `event_id`
- `tenant_id`
- `order_id`
- `idempotency_key`
- `items`, matching the committed order snapshot
- `totals`, including subtotal, discounts, tax, shipping, grand total, and `currency`
- `occurred_at`
- `correlation_id`
- `causation_id`

Customer, shipment, email, analytics, and search events are downstream consumers of `order.confirmed` and remain out of scope for the MVP service-boundary contract.

## Consumer Groups

Consumer group names are stable deployment contracts:

- `checkout.outbox-publisher` publishes committed outbox rows to the transport.
- `checkout.inventory-reservations` consumes inventory reservation requests.
- `checkout.payment-simulator` consumes payment authorization and capture requests.
- `checkout.order-processor` consumes `order.placed` events and confirms orders through the Go order preprocessor boundary.
- `checkout.audit-projection` consumes events for local audit/demo projection.

Consumer names may include the runtime and instance id, for example `order-processor-1`. They are not durable contracts.

Processors must acknowledge a message only after the side effect or projection write commits. If processing fails before acknowledgement, the same message may be delivered again and the idempotency contract below applies.

## Retry And Poison Behavior

- Retries use bounded attempts with exponential backoff and jitter. Start with `3` attempts in local mode unless a worker README documents a narrower bound.
- Retriable failures include transient Redis/MySQL connectivity, lock contention, and timeout from a local simulator dependency.
- Non-retriable failures include invalid schema version, missing required envelope fields, tenant lookup failure, unsafe payload, and deterministic simulator rejection.
- A poison message is one that exceeds retry attempts or fails non-retriable validation. Move or copy it to a poison stream or table with `eventId`, `eventType`, `consumerGroup`, failure reason, attempt count, and timestamps before acknowledging the original message.
- Poison handling must not block the whole consumer group. The runbook should show how to inspect and replay or discard poison messages once a fix exists.
- Customer-facing checkout confirmation must not wait for retry or poison handling.

## Idempotent Processor Contract

Every processor must:

- Treat `eventId` as the transport dedupe key and `idempotencyKey` as the business side-effect key.
- Persist processed event ids or business keys before acknowledging messages.
- Return success without repeating the side effect when a duplicate event or idempotency key is observed.
- Scope all dedupe records by tenant and processor name.
- Emit follow-up events with the original `correlationId`, triggering `eventId` as `causationId`, and a deterministic idempotency key.
- Keep external side effects behind deterministic local simulators in Phase 3.

## Rules

- Events are published from a committed outbox row.
- Event handlers must be idempotent.
- Customer-facing checkout responses must not depend on analytics, email, search, or observability projections.
- Events for the same aggregate should preserve commit order.
- Payload schemas are versioned with `schemaVersion`.
- Failed outbox publishes increment retry metadata and leave `published_at` empty.
- Rows with `next_publish_at` in the future are not publishable until that timestamp.
- Rows with `poisoned_at` are poison messages and the publisher skips them until an operator or repair process clears the poison state.
- Processor writes must be tenant-scoped and must not trust tenant identifiers from unverified headers or path segments.
- Event names use dotted lower-case names on the wire. JSON fields in application code may use local language conventions, but published envelopes must map to the required event fields above.

## Outbox Row Shape

- `id`
- `event_id`
- `event_type`
- `aggregate_type`
- `aggregate_id`
- `tenant_record_id`
- `payload`
- `published_at`
- `publish_attempts`
- `next_publish_at`
- `last_publish_attempted_at`
- `last_publish_error`
- `poisoned_at`

`created_at` is the current occurrence timestamp. `publish_attempts` counts failed publish attempts. `next_publish_at` schedules the next retry. `last_publish_attempted_at` and `last_publish_error` preserve the last failure context. `poisoned_at` marks a poison message after the publisher exhausts its retry ceiling.
