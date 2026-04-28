# Checkout MVP Work Plan

## Delivery Model

Use agile slices: BDD scenario, failing Pest test where applicable, smallest
implementation, validation, then integration. Keep local/dev mode free or
near-free, and keep deploy mode optional until explicitly approved.

## Delivery Roles

- Atlas, Architecture Agent: service boundaries, ADRs, diagrams, DDD boundaries,
  and non-functional requirements.
- Loom, Laravel Checkout Agent: public API, Blade UI, RoadRunner config,
  persistence, idempotency, and validation.
- Forge, Platform Agent: Docker, local Kubernetes direction, Terraform, GitLab
  CI/CD, mirroring, secrets strategy, and deployment docs.
- Beacon, Observability Agent: OpenTelemetry traces, logs, metrics, dashboards,
  SLOs, and load-test interpretation.
- Quill, Contracts Agent: public API contracts, proto contracts, RFC 9457 Problem
  Details shapes, and contract examples.
- Sprout, Data And Seed Agent: tenant fixtures, catalog/product/cart/checkout
  seed data, and local data reset workflows.
- Hammer, Go Services Agent: future Go workers/services after the Laravel happy
  path has a clear extraction reason.
- Shield, Security Agent: IAM, network boundaries, secrets, tenant isolation,
  payment simulation boundaries, and container scanning.
- Gauge, QA And Load Agent: integration tests, contract tests, smoke tests, and
  throughput/latency scenarios.

## Phase Structure

### Phase 0: Scaffolding, Risks, Guardrails, And AI Tooling

- Create source-control, branch, mirror, and agent guidance.
- Add docs/scaffolding only; do not build full checkout behavior.
- Add CI placeholders, local tool guidance, runbooks, risk register, ADRs, and
  architecture skeletons.
- Keep secrets out of the repository and keep AWS deployment unapproved.

### Phase 1: Repository Foundation And Contracts

- Create monorepo layout, service placeholders, shared docs, Docker Compose,
  proto placeholders, and Laravel app structure.
- Define tenant model, checkout state, Problem Details, domain events, seed
  data, latency SLOs, BDD/TDD workflow, and API contracts.
- Build the first Laravel + Blade MVVM-style UI for two tenants with seeded
  product listing, product detail, cart, checkout, and confirmation screens.
- Keep Go service directories placeholders until extraction is justified.

### Phase 2: Local Runnable Checkout Path

- Implement the local checkout API and UI baseline.
- Add MySQL-backed tenant/catalog/cart/checkout/order state, seeders, idempotent
  order confirmation, Redis Streams, and transactional outbox publishing.
- Keep default local runtime fast with Nginx/PHP-FPM and expose RoadRunner and
  parity profiles only as explicit modes.
- Close this phase as the system-completion baseline once demo and validation
  evidence exists.

### Phase 3: Peripheral Services And Boundary Proof

- Pivot from deeper Laravel-internal worker expansion to a small architecture
  proof.
- Keep Laravel as the shopper-facing frontend/BFF and checkout owner.
- Prove selected Go boundaries for inventory reservation and order
  preprocessing only where contracts, concurrency, and long-running processing
  justify them.
- Demonstrate or document the MQ boundary from Laravel committed outbox events
  to Redis Streams locally and SQS/SNS in deploy mode.

### Phase 4: Observability, Performance, And Read Models

- Add full OpenTelemetry metrics/traces, JSON logs, trace/request IDs, tenant
  tags, latency histograms, and dashboard docs.
- Add Problem Details middleware/packages and shared cross-cutting adapters.
- Add search/read-model projection workers after event retry and replay behavior
  are stable.
- Add load tests for the one-second checkout SLO under stated conditions.

### Phase 5: Optional AWS Deployment

- Add Terraform and Kubernetes overlays for optional EKS, RDS MySQL,
  ElastiCache, OpenSearch, IAM, networking, budgets, and telemetry exporter
  profiles.
- Default deployment workflows to plan/build/test and require manual approval
  for cloud actions.
- Do not deploy from GitHub Actions.

### Phase 6: Demo Polish

- Finalize seed data, demo scripts, API collection, architecture docs, ADRs,
  cost notes, and walkthrough material.
- Keep tradeoff summaries current for monorepo/polyrepo, RoadRunner/PHP-FPM,
  REST/gRPC, OpenSearch/Elastic Cloud, and sync/async checkout decisions.

## Sensible Defaults

- Payment is simulated.
- PHP/Laravel is the default for checkout orchestration and domain-heavy
  business flow.
- Go is reserved for selected internal services/processors.
- External API is REST; internal service calls are gRPC only after extraction.
- Initial UI is Laravel Blade with explicit view models.
- Shopping and checkout do not require login.
- Tenancy is shared-database with strict tenant scoping.
- Checkout/order writes use ACID transactions; projections and side effects use
  eventual consistency.
- OpenTelemetry/OTLP is the standard telemetry interface.
- RFC 9457 Problem Details is the standard HTTP error format.

## References

- [PRD](../prd/checkout-mvp-prd.md)
- [Architecture design](../design/checkout-mvp-architecture-design.md)
- [ADR index](../adr/README.md)
- [Phase 1 foundation plan](phase-1-foundation-plan.md)
- [Phase 2 system completion plan](phase-2-system-completion-plan.md)
- [Phase 3 boundary proof plan](phase-3-boundary-proof-plan.md)
