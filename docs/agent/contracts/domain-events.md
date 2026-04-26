# Domain Events

Domain events record committed business facts and feed local Redis Streams or deploy-mode messaging.

## Phase 1 Minimum Events

- `checkout.state.created`
- `checkout.address.updated`
- `checkout.shipping_option.selected`
- `checkout.payment_method.selected`
- `checkout.order.confirmation_requested`
- `order.confirmed`

## Later Events

- `inventory.reservation.requested`
- `payment.settlement.simulated`
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
  "traceId": "01HV...",
  "payload": {}
}
```

## Rules

- Events are published from a committed outbox row.
- Event handlers must be idempotent.
- Customer-facing checkout responses must not depend on analytics, email, search, or observability projections.
- Events for the same aggregate should preserve commit order.
- Payload schemas are versioned with `schemaVersion`.

## Outbox Row Shape

- `id`
- `event_id`
- `event_type`
- `aggregate_type`
- `aggregate_id`
- `tenant_record_id`
- `payload`
- `published_at`

`created_at` is the current occurrence timestamp. Add retry/error metadata in the same migration that introduces a retrying publisher.
