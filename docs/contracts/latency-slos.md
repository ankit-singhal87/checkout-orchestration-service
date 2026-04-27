# Latency SLOs

The MVP documents SLOs as engineering targets, not contractual SLAs.

## Public API Targets

- `GET /api/checkout/config`: p95 under `100ms`, cacheable by tenant.
- Product listing/card reads: p95 under `250ms`, cacheable when personalized state is absent.
- `PUT /api/checkout/state`: p95 under `500ms`.
- `GET /api/checkout/state`: p95 under `500ms`.
- Address, shipping, and payment selection: p95 under `700ms`.
- Order confirmation: p95 under `900ms`.

## System Targets

- Public API warm-path p95 under `1000ms`.
- No duplicate order for the same idempotency key.
- Zero cross-tenant reads/writes in tenant isolation tests.
- Every public request has request ID, trace ID, route, status, latency, and safe tenant context.
- Local outbox publish lag p95 under `5s` while the worker stack is healthy.
- Local worker processing p95 under `2s` per message for deterministic simulator processors.
- Poison-message isolation under `10s` after the final failed attempt in local mode.

## Measurement Rules

- State the load profile before claiming an SLO result.
- Measure locally first, then repeat in deploy mode only after cloud guardrails exist.
- Do not count async email, analytics, search indexing, or observability projections in the customer-facing order confirmation latency.
- Report worker SLOs by processor and include consumer group, event type, status, attempt count, tenant, and latency fields.
