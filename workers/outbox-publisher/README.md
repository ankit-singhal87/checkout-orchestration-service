# Outbox Publisher

Publishes committed domain events from the database outbox to local Redis Streams or deploy-mode messaging.

## Phase 1 Scope

- Define the outbox ownership and transport mapping.
- Keep the implementation choice open: Laravel worker first, Go worker later if throughput or operational needs justify it.

## Local Runtime

The first local runtime is the Docker Compose `checkout-outbox-worker` service. It reuses the checkout PHP image and runs `scripts/dev/start-outbox-worker.sh`, a polling loop around the existing Laravel `checkout:outbox:publish` command.

Start it with:

```bash
make up-outbox-worker
```

The worker exits on publisher command failure so Compose can apply its restart policy. It does not implement inventory, payment, notification, or read-model processors.

The order processor is a separate consumer. Start it with `make up-order-processor`; it reads the `checkout:events` stream through consumer group `checkout.order-processor` and currently projects consumed `order.confirmed` envelopes into `order_processor_processed_events`, `order_processor_audit_events`, and `order_processor_poison_events`.

That runtime is current scaffold and prior implementation baseline. The target architecture keeps the outbox publisher transport-only while the Go order preprocessor consumes `order.placed` and emits `order.confirmed`.

## Transports

- Local/dev: Redis Streams.
- Deploy mode: AWS SQS/SNS, after cloud guardrails exist.

## Phase 3 Contract

- Source: committed MySQL outbox rows.
- Local stream: `checkout:events`.
- Publisher identity: `checkout.outbox-publisher`.
- Message body: the event envelope defined in [domain-events.md](../../docs/contracts/domain-events.md).
- Ordering: preserve commit order per aggregate where feasible; never reorder events for the same aggregate intentionally.
- Publish acknowledgement: mark `published_at` only after the transport write succeeds.

## Retry And Poison

- Track publish attempts and last error with the outbox row when retrying publisher behavior is introduced.
- Retry transient transport failures with bounded exponential backoff and jitter.
- Mark or move permanently invalid rows to the poison path with event id, event type, tenant record id, attempts, and error reason.
- Redis unavailability must not roll back checkout/order commits.

## MVP Event Streams

- `order.placed` is emitted after Laravel checkout commits the order placement intent.
- `order.confirmed` is emitted after the Go order preprocessor materializes inventory through the Go inventory service and saves the durable order outcome.

The publisher preserves the committed outbox envelope and does not enrich events with customer, shipment, or search-specific payloads. Those concerns belong to downstream consumers.

## Incremental Tasks

1. Validate the envelope fields emitted for `order.placed` and `order.confirmed`.
2. Add retry metadata and tests around transient Redis failure.
3. Add poison isolation for invalid payload/schema rows.
4. Add worker/runbook evidence for stream inspection and replay-safe consumption.
5. Keep customer, shipment, email, analytics, and search behavior downstream of `order.confirmed`.
