# Phase 2/3 Demo Runbook

Use this runbook to show the current local system-completion story: tenant-aware checkout, transactional order/outbox writes, Redis Streams publication, order processor consumption, request correlation, and validation. Phase 3 extends the same path with richer retry/poison evidence and deterministic inventory/payment simulators as those lanes land.

## Start Local Services

```bash
make up-app
```

The default local path uses Nginx/PHP-FPM and listens on `http://localhost:8080`.

Open both demo tenants:

- `http://fashion-demo.localhost:8080/shop`
- `http://sports-demo.localhost:8080/shop`

For the local-production parity path:

```bash
make up-parity
```

The Caddy edge listens on `https://localhost:8443` with Caddy local certificates and HTTP/1.1, HTTP/2, and HTTP/3 over QUIC/UDP 443:

- `https://fashion-demo.localhost:8443/shop`
- `https://sports-demo.localhost:8443/shop`

The equivalent direct Compose form is:

```bash
docker compose -f docker-compose.yml -f docker-compose.caddy.yml up
```

For the optional RoadRunner/Octane performance profile:

```bash
make up-roadrunner
```

RoadRunner listens on `http://localhost:8082` by default.

## Walk A Checkout

For each tenant:

1. Open the tenant shop URL.
2. Add a product to the cart.
3. Continue through guest checkout.
4. Enter demo address, shipping option, and payment method.
5. Confirm the order.

Order confirmation creates an `orders` row and a committed `outbox_events` row. Redis availability must not decide whether the order exists.

## Publish Outbox Events

Publish unpublished outbox rows to the local Redis Stream:

```bash
make demo-outbox-publish
```

Inspect recent events:

```bash
make demo-redis-events
```

Expected stream: `checkout:events`.

## Consume Order Events

Start the local outbox worker and order processor runtime:

```bash
make up-outbox-worker
make up-order-processor
```

The order processor runs `php artisan checkout:order-processor:consume` in the `checkout-order-processor` Compose service. It consumes `order.confirmed` envelopes from `checkout:events` with consumer group `checkout.order-processor`, records replay protection in `order_processor_processed_events`, writes invalid envelopes to `order_processor_poison_events`, and maintains the rebuildable audit projection in `order_processor_audit_events`.

Phase 3 worker evidence should show:

- Envelope fields from [domain-events.md](contracts/domain-events.md), including event id, tenant, trace/request ids, correlation id, causation id, and idempotency key.
- Consumer group names such as `checkout.order-processor`, with inventory and payment simulator groups added by their lanes.
- Duplicate delivery replaying safely without duplicate inventory, payment, notification, or audit side effects.
- Poison-message isolation for invalid envelopes or exhausted retry attempts.
- Deterministic inventory/payment simulator results derived from tenant-scoped fixture state and idempotency keys.

The current smoke target is command registration for the order processor runtime:

```bash
make test-order-processor-runtime
```

An end-to-end event pipeline smoke can be added later once the processor lanes share one stable fixture path; do not assume that command exists yet.

## Show Correlation

Call an API endpoint with a fixed request ID:

```bash
curl -i -H 'X-Request-Id: demo-request-1' \
  http://fashion-demo.localhost:8080/api/checkout/config
```

Inspect checkout logs for the same request/correlation fields:

```bash
docker compose logs checkout | rg 'demo-request-1|request_id|trace_id'
```

Demoable now:

- `X-Request-Id` and `X-Trace-Id` response headers.
- Problem Details payloads containing trace IDs where implemented.
- Structured JSON HTTP completion logs on container stderr.

Future work:

- Application OTLP trace export.
- Log shipping to Loki.
- Grafana dashboards and provisioned datasources.
- Worker metrics for publish lag, processing latency, attempts, poison count, and processor status.

## Optional Observability Stack

The observability profile starts collector and local backends, but the application does not yet export traces or logs to them.

```bash
make up-observability
curl -fsS http://localhost:9090/-/ready
curl -fsS http://localhost:3100/ready
curl -fsS http://localhost:3200/ready
```

## Validation

```bash
make validate
make test-checkout
make test-checkout-runtime
make test-checkout-parity
make test-order-processor-runtime
make pre-push-full
```
