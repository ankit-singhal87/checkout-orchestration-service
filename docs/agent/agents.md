# Project Agent Lanes

Named lanes describe repository ownership boundaries. They are not separate
services, and they do not redefine Codex workflow mechanics.

## Coordination Rules

- The active Cursor/Codex session is the `lead-orchestrator` unless the user
  explicitly assigns that role elsewhere.
- Keep one active `lead-orchestrator`; if ownership is unclear, collect
  read-only status and ask before editing, branching, committing, pushing, or
  preparing merge requests.
- Assign implementation, validation, integration, commits, pushes, and merge
  request preparation to the narrowest safe lane.
- Give editable work an explicit path list, validation command, stop condition,
  and branch/worktree when parallel work could collide.
- Do not let two active branches edit the same files at the same time.
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

Owns future Go workers/services for outbox publishing, order processing,
inventory reservation, payment simulation, search indexing, and analytics
consumers. Hammer should not extract services before the Laravel happy path is
stable and a clear boundary exists.

### Relay - Integration And Release

Owns branch hygiene, integration mechanics, validation coordination, commits,
pushes, and authorized GitLab merge request preparation.

### Observer - Status

Owns read-only branch, merge request, merge, CI, pipeline, and command-output
status checks. Observer reports state and blockers only.

### Scribe - Context And Documentation

Owns compact handoffs, durable doc routing, stale context cleanup, and removal
of obsolete branch-specific notes after merge or abandonment.

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
