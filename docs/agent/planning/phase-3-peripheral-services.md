# Phase 3 Peripheral Services and Workers

Phase 3 is the active implementation focus. Phase 2 is closed as the local runnable checkout/system-completion baseline: Laravel checkout flow, Redis Streams outbox publishing, demo runbook commands, Nginx/PHP-FPM default runtime, optional RoadRunner profile, and Caddy HTTPS/H1/H2/H3 edge parity.

Phase 3 should build peripheral services and workers around the existing Laravel checkout core without prematurely splitting the domain into deployable microservices. The default direction is: keep checkout orchestration in Laravel, add reliable async processing, then extract only the worker/service boundaries that prove useful.

## Priority Order

1. **Core checkout hardening:** finish the Laravel behavior needed to produce trustworthy worker inputs: tenant-safe checkout state, idempotent order creation, inventory/payment simulator interfaces, Problem Details coverage, and tests for double-submit and tenant isolation.
2. **Async backbone:** harden MySQL outbox to Redis Streams delivery, message envelope fields, consumer groups, retry policy, poison message handling, idempotent processors, and trace/request propagation.
3. **Worker runtime:** run local worker processes in Docker Compose with health checks, restart policy, Make targets, and CI smoke coverage for event publish and consume.
4. **First peripheral workers:** implement inventory reservation, payment authorization/capture simulation, order confirmation side effects, notification stubs, and audit/event journal projection as small processors.
5. **Read-model projections:** add search/catalog/order projection workers only after the event envelope and retry behavior are stable.
6. **Service extraction review:** consider Go only for processors with clear concurrency, async throughput, or latency value. Keep modules in Laravel until the boundary earns a separate runtime.

## Incremental Backlog

1. Freeze the Phase 3 event envelope, consumer group names, retry/poison rules, and idempotent processor contract in [domain-events.md](../contracts/domain-events.md).
2. Add focused tests or smoke scripts that prove `order.confirmed` is published once and can be consumed idempotently.
3. Add the local worker runtime around the existing Laravel/Redis Streams path with health checks and restart behavior.
4. Implement the outbox publisher retry metadata and poison isolation without changing checkout response semantics.
5. Implement the deterministic inventory reservation processor against tenant-scoped seeded stock.
6. Implement deterministic payment authorization/capture simulation with no real payment credentials, external webhooks, or provider calls.
7. Add order confirmation notification and audit projection stubs after the first simulator processor is replay-safe.
8. Add read-model/search projections only after retry, poison, and replay behavior have validation coverage.
9. Review service extraction only after a processor shows measured concurrency, throughput, or latency pressure that Laravel workers cannot meet cleanly.

Do not pull Go extraction forward just to create a service boundary. Phase 3 favors stable contracts, Laravel-first processors, and small replay-safe workers before any runtime split.

## Parallel Work Lanes

These lanes can run concurrently when their editable paths do not overlap:

- **Core Checkout:** Laravel routes, services, models, Pest tests, and contracts for checkout/order invariants.
- **Async Backbone:** outbox publisher, Redis Stream envelope, consumer group conventions, retry/poison handling, and stream tests.
- **Worker Runtime:** Docker Compose worker containers, startup scripts, Make targets, and CI smoke jobs. Coordinate this lane with any Caddy/Compose work before editing.
- **Inventory and Payment Simulators:** deterministic reservation and payment processors with clear interfaces and tests.
- **Observability Contracts:** request/event trace propagation, log fields, metric names, and runbook evidence. Implementation should coordinate with middleware and worker lanes.
- **Contracts and Docs:** OpenAPI/async contracts, state transition docs, worker retry contract, and runbooks.
- **Security Review:** tenant isolation, trusted proxy/forwarded headers, sessions, rate-limit boundaries, idempotency, and event data exposure. Start read-only before changing code.

## Phase 3 Acceptance Criteria

- A confirmed order emits a durable event that at least one worker consumes idempotently.
- Worker retry and poison-message behavior is documented and covered by focused tests or smoke scripts.
- Inventory and payment simulation are deterministic and tenant-safe.
- Local Docker Compose can run the web stack plus at least one worker process.
- CI has smoke coverage for the worker/event path without materially slowing unrelated validation.
- Observability fields include request ID, trace ID, tenant, event ID, route/processor, status, and latency where applicable.

## Deferred to Phase 4+

- Full OpenTelemetry metrics/traces and provider-specific dashboards.
- OpenSearch production read-model depth beyond initial projections.
- Go service extraction that does not yet have a proven async, concurrency, or latency need.
- EKS, Terraform apply, registry publishing, cloud queues, managed OpenSearch, managed observability exporters, and deploy automation.
- Loyalty, collection points, address book, vouchers, carrier integrations, real payment providers, and full SCAYLE-shaped API breadth.
