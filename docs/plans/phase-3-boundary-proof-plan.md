# Phase 3 Architecture Pivot and Boundary Proof

Phase 3 is the active implementation focus. Phase 2 is closed as the local runnable checkout/system-completion baseline: Laravel checkout flow, Redis Streams outbox publishing, demo runbook commands, Nginx/PHP-FPM default runtime, optional RoadRunner profile, and Caddy HTTPS/H1/H2/H3 edge parity.

Phase 3 now pivots from deeper Laravel-internal worker platform work to a small architecture proof. The goal is to demonstrate the high-signal boundaries of the intended MVP: Laravel remains the shopper-facing frontend/BFF and checkout orchestrator, while selected Go services prove inventory and order preprocessing boundaries, messaging proves the event seam, and docs/contracts make the tenant, identity, read-path, and rate-limit strategy explicit.

Current Laravel worker/outbox branches are useful scaffold and learning state. They show local outbox publishing, event envelope concerns, worker runtime smoke coverage, and retry/consumer safety issues. They are not the target MVP architecture by themselves, and Phase 3 should not keep deepening that internal worker platform before the service boundaries are reset.

## MVP Stop Line

Stop the MVP once docs and skeletal implementation are sufficient to demonstrate:

- Laravel frontend/BFF ownership of Blade UI, public checkout APIs, tenant resolution, checkout orchestration, Problem Details, and idempotent confirmation.
- Go Inventory Service boundary for tenant-scoped stock reservation/release with gRPC contracts and MySQL-backed source-of-truth strategy.
- Go Order Preprocessor boundary for validating and preparing committed checkout/order events before downstream side effects.
- MQ event boundary from Laravel committed outbox events to local Redis Streams and deploy-mode SQS/SNS mapping.
- Tenant registry and data-plane strategy: verified domains or signed token claims resolve tenant identity; shared MySQL schemas stay tenant-scoped; future isolation options remain documented, not implemented.
- Keycloak identity model: local optional identity provider, realm/client/role mapping, customer account as optional checkout enhancement, and no auth requirement for guest checkout.
- OpenSearch/CloudFront read path: product/config/static reads are cacheable/projection-backed; checkout/order writes remain MySQL/Redis transactional paths.
- Tenant rate-limiting concept: Redis-backed limits keyed by tenant, route, IP/customer/session, and propagated consistently across Laravel and Go boundaries.

Customer profile management, shipment/carrier flows, loyalty, collection points, address book breadth, real payment integrations, production OpenSearch depth, and cloud deployment are outside this MVP stop line.

## Priority Order

1. **ADR/contracts reset:** align architecture docs, public/internal contracts, async event contracts, tenant registry strategy, identity model, read-path strategy, and rate-limit concept around the pivot.
2. **Proto skeleton:** add minimal gRPC/protobuf shape for inventory reservation/release and order preprocessing, with examples and compatibility notes.
3. **Go inventory skeleton:** create a thin service boundary with tenant-scoped reserve/release APIs, local persistence strategy, health checks, and focused tests; no full inventory platform.
4. **Go order preprocessor skeleton:** consume/accept order-confirmed intent, validate tenant/idempotency/envelope metadata, and emit deterministic prepared-order output for later side effects.
5. **Laravel boundary integration:** keep Laravel as FE/BFF and checkout owner while wiring only the boundary calls/events needed for the proof; avoid rebuilding internal worker platform breadth.
6. **Docs/demo narrative:** document the end-to-end demo path, what is real, what is simulated, what remains deferred, and why the stop line prevents scope creep.

## Incremental Backlog

1. Freeze the Phase 3 event envelope, consumer group names, retry/poison rules, and idempotent processor contract in [domain-events.md](../../docs/contracts/domain-events.md).
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

- **Architecture and ADRs:** service boundaries, deploy/local mode split, tenant registry/data-plane, identity, read path, and rate limits.
- **Contracts:** OpenAPI deltas, async event examples, proto skeleton, Problem Details mapping, and compatibility notes.
- **Inventory Service:** Go skeleton, gRPC handlers, persistence shape, tenant-safe tests, and local runtime wiring.
- **Order Preprocessor:** Go skeleton, event intake, idempotency/envelope validation, prepared-order output, and smoke tests.
- **Laravel Boundary Integration:** Laravel BFF calls/events, checkout state invariants, tenant context propagation, and small Pest coverage.
- **Docs/Demo Narrative:** runbook, diagram updates, MVP stop line, and explicit deferred scope.
- **Security Review:** tenant isolation, trusted proxy/forwarded headers, Keycloak assumptions, rate-limit boundaries, idempotency, and event data exposure. Start read-only before changing code.

## Phase 3 Acceptance Criteria

- ADRs/contracts describe the pivot clearly enough that later workers do not continue the old Laravel-internal worker-platform path by default.
- Laravel remains the user-facing FE/BFF and order-confirmation owner, with only the minimal boundary calls/events needed for the proof.
- Go inventory skeleton exposes tenant-scoped reserve/release behavior through the agreed proto shape and documents MySQL ownership.
- Go order preprocessor skeleton validates event metadata and produces deterministic prepared-order output without taking ownership of order creation.
- MQ boundary is demonstrated or documented from Laravel committed outbox to Redis Streams locally, with SQS/SNS deploy mapping left as a contract.
- Tenant registry, Keycloak identity model, OpenSearch/CloudFront read path, and tenant rate-limiting concept are documented with clear implemented/deferred labels.
- Customer management, shipment/carrier flows, real payment providers, and broad worker projections remain explicitly deferred.

## Deferred to Phase 4+

- Full OpenTelemetry metrics/traces and provider-specific dashboards.
- OpenSearch production read-model depth beyond initial projections.
- Go service extraction beyond inventory and order preprocessing.
- EKS, Terraform apply, registry publishing, cloud queues, managed OpenSearch, managed observability exporters, and deploy automation.
- Customer profile management, shipments/carriers, loyalty, collection points, address book, vouchers, real payment providers, and broad commerce API breadth.
