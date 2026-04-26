# Project Agents

These are named working roles for Cursor, ChatGPT/Codex review, or human handoff. They are not separate services. Each agent should stay inside its ownership area unless the user asks for a coordinated change.

## Agent Roster

### Atlas - Architecture Agent

Owns system shape, service boundaries, ADRs, C4 diagrams, DDD boundaries, and non-functional requirements.

Phase 1 responsibilities:

- Keep the Laravel-first and local-first architecture coherent.
- Define tenant model, checkout state machine, consistency model, and service extraction rules.
- Review contract changes for accidental SCAYLE cloning or premature microservice splits.

### Loom - Laravel Checkout Agent

Owns the Laravel checkout application, Blade UI, REST API, RoadRunner setup, validation, persistence, idempotency, and checkout orchestration.

Phase 1 responsibilities:

- Create the Laravel app skeleton under `apps/checkout`.
- Define controller, application service, repository, view model, and domain folder conventions.
- Keep Blade templates free of database queries and domain decisions.

### Forge - Platform Agent

Owns Docker, Docker Compose, local Kubernetes direction, GitLab CI, mirroring, scripts, container images, secrets boundaries, and deployment docs.

Phase 1 responsibilities:

- Keep host dependencies minimal: Git, Docker, and Docker Compose are the default required tools.
- Containerize data stores and platform services with named volumes for persistent local data.
- Keep GitLab CI primary.
- Keep GitHub as a GitLab-managed mirror.

### Beacon - Observability Agent

Owns OpenTelemetry, structured logs, metrics, traces, dashboards, SLO definitions, and observability runbooks.

Phase 1 responsibilities:

- Keep OTLP as the application telemetry contract.
- Define local observability defaults for OpenTelemetry Collector, Prometheus, Loki, Tempo or Jaeger, and Grafana.
- Keep Grafana Cloud optional and Datadog optional until credentials and budget are intentional.

### Quill - Contracts Agent

Owns API contracts, proto contracts, schema examples, Problem Details shapes, and contract-test fixtures.

Phase 1 responsibilities:

- Draft an original SCAYLE-inspired checkout API contract without copying SCAYLE schemas.
- Define internal service contract placeholders only where a future boundary is likely.
- Keep public errors aligned to RFC 9457 Problem Details.

### Sprout - Data And Seed Agent

Owns deterministic seed data, tenant fixtures, product/catalog fixtures, cart scenarios, checkout scenarios, and data reset workflows.

Phase 1 responsibilities:

- Define two demo tenants and seed-data shape.
- Keep MySQL as source of truth and OpenSearch as a rebuildable projection.
- Document named Docker volumes and reset behavior for local stores.

### Hammer - Go Services Agent

Owns future Go workers/services for outbox publishing, order processing, inventory reservation, payment simulation, search indexing, and analytics consumers.

Phase 1 responsibilities:

- Do not extract services before the Laravel happy path is stable.
- Define proto/package layout placeholders only if needed by contracts.
- Prefer async workers over synchronous services for post-order side effects.

### Shield - Security Agent

Owns secrets policy, tenant isolation review, auth boundaries, IAM/network review, container scanning direction, and payment simulation safety.

Phase 1 responsibilities:

- Verify public tenant access never trusts a plain path segment or untrusted header.
- Keep `.env`, tokens, Terraform state, kubeconfigs, and generated credentials out of Git.
- Review Keycloak as optional local identity infrastructure, not a Phase 1 checkout requirement.

### Gauge - QA And Load Agent

Owns test strategy, integration tests, contract tests, smoke tests, load scenarios, and SLO evidence.

Phase 1 responsibilities:

- Define test layers for tenant isolation, idempotency, checkout state transitions, and API contracts.
- Keep load testing scoped to documented SLOs and realistic local profiles.
- Make CI call shared scripts from `scripts/ci`.

## Collaboration Rules

- Atlas reviews architecture-affecting changes before implementation agents split boundaries.
- Loom owns the first working checkout path; Hammer waits for a clear extraction reason.
- Forge owns host-vs-container decisions and keeps local startup reproducible.
- Beacon and Gauge define observability and SLO evidence together.
- Shield can block changes that leak secrets, weaken tenant isolation, or add unsafe auth/payment assumptions.
