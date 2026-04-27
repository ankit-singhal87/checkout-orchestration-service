# Go Service Standards

These standards apply to MVP Go services and workers only after a boundary is intentionally introduced.

- Use `context.Context` on every external, database, cache, and message operation.
- Set explicit deadlines or timeouts at service boundaries; do not rely on unbounded default contexts.
- Require `tenant_id` at every public service, worker, message, and storage boundary.
- Make inventory commands idempotent by `tenant_id` and `order_id`.
- Make order event handling idempotent by `tenant_id`, `order_id`, and `event_id` or `idempotency_key`.
- Use structured logs with request, trace, tenant, order, event, status, and latency fields where available.
- Return typed errors that callers can map to Problem Details or retry decisions.
- Do not commit generated Go or proto code until generation tooling is approved.
- Do not split speculative microservices from Laravel; extract only when concurrency, async processing, or latency pressure justifies it.
