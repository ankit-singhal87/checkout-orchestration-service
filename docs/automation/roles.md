# Ownership Lanes

Named lanes describe repository ownership boundaries. They are not separate
services.

## Rules

- Use the narrowest lane that owns the files or behavior being changed.
- Give production-adjacent work an explicit path list, validation command, and
  stop condition.
- Do not let parallel work edit the same files at the same time.
- Use Makefile targets where available, especially `make validate`,
  `make test-checkout`, `make pre-push`, and `make pre-push-full`.

## Stop Conditions

Stop and report instead of improvising when work needs:

- Secrets, credentials, cloud billing, or external account access.
- Destructive Git operations.
- Schema or contract decisions that affect multiple phases.
- Tenant isolation judgment that is not explicit in existing contracts.
- Changes outside the assigned path list.
- A failing validation command with an unclear cause.
- Product or architecture decisions that conflict with phase docs.

## Lane Roster

### Atlas - Architecture

Owns system shape, service boundaries, ADRs, C4 diagrams, DDD boundaries, and
non-functional requirements.

### Loom - Laravel Checkout

Owns the Laravel checkout app, Blade UI, REST API, validation, persistence,
idempotency, and checkout orchestration.

### Forge - Platform

Owns Docker, Docker Compose, local Kubernetes direction, GitLab CI, scripts,
container images, secrets boundaries, and deployment docs.

### Beacon - Observability

Owns OpenTelemetry, structured logs, metrics, traces, dashboards, SLO
definitions, and observability runbooks.

### Quill - Contracts

Owns API contracts, proto contracts, schema examples, Problem Details shapes,
and contract-test fixtures.

### Sprout - Data And Seed

Owns deterministic seed data, tenant fixtures, product/catalog fixtures, cart
scenarios, checkout scenarios, and data reset workflows.

### Hammer - Go Services

Owns future Go workers/services for inventory reservation and order
preprocessing first, with later payment simulation, search indexing, and
analytics consumers only after boundaries are explicit. The current outbox
publisher and order processor are Laravel scaffold/runtime support until the
Go boundaries replace the relevant target behavior.

### Shield - Security

Owns secrets policy, tenant isolation review, auth boundaries, IAM/network
review, container scanning direction, and payment simulation safety.

### Gauge - QA And Load

Owns test strategy, integration tests, contract tests, smoke tests, load
scenarios, and SLO evidence.

## Collaboration Boundaries

- Atlas reviews architecture-affecting changes before implementation lanes split
  boundaries.
- Loom owns the first working checkout path; Hammer waits for a clear extraction
  reason.
- Forge owns host-vs-container decisions and local startup reproducibility.
- Beacon and Gauge define observability and SLO evidence together.
- Shield can block changes that leak secrets, weaken tenant isolation, or add
  unsafe auth/payment assumptions.
