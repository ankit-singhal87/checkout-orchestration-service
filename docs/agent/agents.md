# Project Agents

These are named working roles for Cursor, ChatGPT/Codex review, or human handoff. They are not separate services. Each agent should stay inside its ownership area unless the user asks for a coordinated change.

## Operating Model

Use one lead orchestrator and several bounded worker agents.

- The lead orchestrator owns only coordination: current intent, task slicing, worker assignment, conflict prevention, stop-condition escalation, and context defragmentation.
- Worker agents own bounded work packages with explicit allowed paths, expected outputs, validation commands, and stop conditions.
- The orchestrator must not perform implementation, validation, release, or documentation work directly when a named worker owns that lane. It delegates the work and integrates by assigning a worker, not by carrying the execution context itself.
- Agents should read [context-handoff.md](context-handoff.md) after [README.md](README.md) and use it as the compact cross-session memory buffer.
- Agents should use [Makefile](../../Makefile) targets such as `make validate`, `make test-checkout`, `make pre-push`, and `make pre-push-full` for common local workflows. The underlying scripts remain available as fallbacks when `make` is unavailable.
- Agents may suggest `make install-host-tools` for local workstation bootstrap, but must ask before running it because it installs host packages.
- The lead orchestrator should create background worker tasks for independent streams so investigation, review, docs, tests, and isolated implementation can progress in parallel and resolve blockers faster.
- Background agents may explore, review, draft docs, or implement small isolated slices, but they must report findings and changed paths clearly.
- The lead orchestrator must not wait on worker or subagent completion for more than 20 seconds at a time. Treat 20 seconds as the maximum wait, not the default; use shorter 5-10 second polls when not truly blocked, and do not wait when there is independent phase analysis, user-message handling, or another workstream to start. Treat worker waits as a responsive event loop: poll, check newest user intent, reassess phase and priority, then continue waiting, start independent work, close or reassign failed workers, or route another work package as needed.
- Workers should run in the background where possible. Long-running validation, worker waits, and phase analysis must not freeze orchestrator responsiveness.
- Parallel implementation agents may work on their own `agent/<short-scope>` branches when the lead orchestrator assigns an independent lane.
- Agent branches must not edit files owned by another active branch unless the orchestrator explicitly reassigns ownership.
- Agents must follow existing coding standards. When a work package introduces a new implementation technology, the agent should add or update a coding standards document before writing substantial code in that technology.
- GitLab merge request creation is agent-allowed only when the user asks and an approved tool/token is available. Merge remains a human step.

### Lead Orchestrator - Cursor Session Agent

The active Cursor session agent is the lead orchestrator unless the user explicitly assigns that role elsewhere.

Responsibilities:

- Keep the product goal, phase plan, and current branch state in view.
- Convert broad user intent into small agile work packages.
- Decide which worker agents should run in parallel.
- Prevent parallel agents from editing the same files.
- Name and track any parallel `agent/*` branches and decide how each branch is integrated.
- Assign branch creation, code/docs edits, validation, commits, pushes, and merge request preparation to the relevant worker agent.
- Keep context small by assigning Scribe to flush durable 1-2 line learnings into [context-handoff.md](context-handoff.md) after MR creation or merge, then closing or abandoning context-heavy worker threads.
- Stop and ask when worker outputs conflict, when ownership is unclear, or when a requested action crosses a stop condition.

The orchestrator should stay hands-off for execution. Worker agents move independently in their lanes, while the orchestrator owns routing, boundaries, and whether additional workers are needed.

## Agile Working Model

Use small vertical slices rather than waiting for a whole phase to be "done".

- Maintain a near-term backlog of small stories per agent lane.
- Prefer one user-visible checkout behavior per implementation slice.
- Start with BDD scenarios, then Pest tests, then the smallest useful code.
- Keep WIP limited: only one implementation agent should edit a given bounded area at a time.
- Let free agents continue with read-only review, backlog refinement, fixture design, threat modeling, observability plans, or CI hardening in their own sphere.
- Merge specialist work through Relay so validation and release mechanics stay coherent.
- Treat manual GitLab MR creation and merge as the sprint review gate.

Default cadence:

- Plan: orchestrator identifies the next small slice, worker ownership, and applicable coding standards.
- Build: assigned worker implements behind tests, using clean boundaries.
- Verify: Gauge or the owning worker runs local scripts and captures gaps.
- Review: use specialist workers for focused review.
- Integrate: Relay validates branch state, prepares commits, pushes, and creates authorized MRs.
- Defragment: Scribe runs `HANDOFF_LINES="- Durable next-step fact" make defragment-context` to persist only selected facts in [context-handoff.md](context-handoff.md); the orchestrator then closes or abandons the context-heavy worker and starts the next unrelated slice in a fresh session.

## Context Defragmentation

Long agent sessions should be treated as temporary working memory, not the source of truth. Durable information belongs in concise project docs, and the orchestrator owns removal by ending the old worker/session.

There is no project-supported operation for deleting selected history from a live Codex thread. Context removal means: persist selected state, stop using the old worker thread, and start a fresh worker from the compact handoff.

- Use [context-handoff.md](context-handoff.md) for short-lived cross-session memory: current branch/MR state, one-line decisions, and the next likely slice.
- Move lasting decisions into the relevant durable document, such as ADRs, contracts, runbooks, coding standards, or phase planning docs.
- Keep each handoff bullet actionable and no longer than two lines.
- Use `make show-context` to review persisted context and `make defragment-context` with `HANDOFF_LINES` or `HANDOFF_FILE` to replace redundant active context with selected handoff bullets.
- After `make defragment-context`, the current context-heavy agent must stop taking new implementation work. The orchestrator closes that worker if possible, or lets the session end, then starts a fresh worker from [README.md](README.md) and [context-handoff.md](context-handoff.md).
- Do not preserve raw command output unless it is the acceptance evidence for a runbook; summarize the result and link the durable source instead.
- Prefer starting a new agent session after a meaningful MR is created or merged, especially after parallel agents, long command output, or broad doc reads.
- If a session approaches context pressure, stop new implementation, summarize the active branch/MR/tests into [context-handoff.md](context-handoff.md), and continue in a fresh session.

## Autonomy Levels

### Level 0 - Advisory

Agent reads context and returns recommendations only. Use for architecture review, security review, contract review, and unfamiliar-code exploration.

Allowed:

- Read files.
- Search docs/code.
- Produce findings, risks, and next-step recommendations.

Not allowed:

- File edits.
- Commits.
- Pushes.

### Level 1 - Bounded Edit

Agent may edit files inside an explicit path list and run local validation. Use for docs, tests, route stubs, small scripts, and isolated implementation slices.

Required work package fields:

- Objective.
- Allowed paths.
- Out-of-scope paths.
- Acceptance criteria.
- Applicable coding standards.
- Required validation commands.
- Expected final report.

Not allowed unless the user explicitly asks:

- Commits.
- Pushes.
- Dependency changes.
- Changes outside the allowed path list.

### Level 2 - Branch Implementer

Agent may implement a larger vertical slice on a feature branch, run validation, and prepare a commit-ready summary.

Allowed only when:

- The branch name is explicit. For specialist parallel work, prefer `agent/<short-scope>`.
- The slice has clear acceptance criteria.
- CI/local validation commands are known.
- Rollback risk is low or changes are easy to inspect.

Still user-gated:

- Merge request creation unless the user explicitly enables it for the current branch.
- Merge.
- Production/deploy actions.

### Level 3 - Maintenance Loop

Agent may keep a branch merge-ready by responding to CI failures, conflicts, and review comments in a loop.

Use only when the user explicitly asks for this mode. The agent must stop for ambiguous product decisions, destructive Git operations, secret handling, paid cloud operations, or security-sensitive changes.

## Parallel Execution Strategy

Run agents in parallel when workstreams are independent:

- Atlas + Quill: architecture, contracts, API shape, error contracts.
- Loom: Laravel implementation, Blade, routes, application services, Pest tests.
- Forge: Docker, CI, scripts, local runtime.
- Sprout + Gauge: seed data, fixtures, BDD/TDD, concurrency and load tests.
- Shield: tenant isolation, secrets, auth, payment simulation safety.
- Beacon: OpenTelemetry, logs, metrics, traces, SLO evidence.
- Relay: branch integration, validation coordination, commits, pushes, and merge request preparation.
- Scribe: compact handoffs, context defragmentation, durable doc routing, and stale-context cleanup.

Avoid parallel edits to the same files. If two agents need the same file, one should review while the owning worker applies the final edit.

Parallel branch rules:

- Use `agent/<short-scope>` for independent specialist branches.
- Direct foreground implementation should be assigned to a worker on a short-scoped branch instead of accumulating unrelated work in the orchestrator thread.
- Keep each agent branch scoped to its assigned files and acceptance criteria.
- Rebase or merge from GitLab `main` only under orchestrator direction and Relay execution.
- Relay integrates agent branches deliberately and validates the combined tree before user-facing push or MR creation.
- Delete stale `agent/*` branches after their work is integrated or abandoned.

## Main Merge Conflict Playbook

When a feature branch needs to be made current with GitLab `main`, Relay owns the merge and validation under orchestrator direction.

- Start from a clean working tree.
- Fetch GitLab, not the GitHub mirror: `git fetch origin main`.
- Prefer `sh scripts/git/merge-main.sh` for the standard flow.
- If `main` contains an older overlapping Phase 1 scaffold and the feature branch contains the complete later checkout/API/observability implementation, rerun the script with `RESOLVE_PHASE1_SCAFFOLD_CONFLICTS=1`. That guarded mode keeps the current branch side only for the known Phase 1 scaffold conflict set and refuses unknown conflicts.
- Never auto-keep the feature branch side for an existing migration file. Restore tracked migrations to `main` and add a new forward migration for schema changes.
- Do not use broad destructive commands such as `git reset --hard` or `git checkout .`.
- Run `sh scripts/ci/validate-phase1.sh` and `sh scripts/test/checkout-app.sh` before concluding the merge.
- Commit the merge only after validation passes.
- Push only when the user explicitly asks.

## Work Package Template

```markdown
Objective:

Allowed paths:

Out of scope:

Acceptance criteria:

Required validation:

Stop and ask if:

Final report should include:
```

## Stop Conditions

Agents should stop and report rather than improvise when they encounter:

- Required secrets, credentials, cloud billing, or external account access.
- Destructive Git operations.
- Schema or contract decisions that affect multiple phases.
- Tenant isolation uncertainty.
- A failing validation command they cannot explain.
- Needed changes outside the allowed path list.
- Conflicts between phase docs and implementation reality.

## Agent Roster

### Atlas - Architecture Agent

Owns system shape, service boundaries, ADRs, C4 diagrams, DDD boundaries, and non-functional requirements.

Phase 1 responsibilities:

- Keep the Laravel-first and local-first architecture coherent.
- Define tenant model, checkout state machine, consistency model, and service extraction rules.
- Review contract changes for accidental SCAYLE cloning or premature microservice splits.

### Loom - Laravel Checkout Agent

Owns the Laravel checkout application, Blade UI, REST API, validation, persistence, idempotency, and checkout orchestration. RoadRunner runtime wiring is a later platform/runtime slice.

Phase 1 responsibilities:

- Create the Laravel app skeleton under [apps/checkout](../../apps/checkout).
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
- Keep local development free of a required observability backend.
- Defer the concrete backend choice. Grafana Cloud, Datadog, Dash0, and a self-hosted Grafana stack remain profile options until credentials, budget, and demo goals are intentional.

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

### Relay - Integration And Release Agent

Owns branch hygiene, integration mechanics, validation coordination, commits, pushes, and GitLab merge request preparation.

Phase 1 responsibilities:

- Create or switch short-scoped branches when assigned by the orchestrator.
- Integrate worker outputs through review, cherry-pick, merge, or a GitLab merge request.
- Run `make validate`, `make pre-push`, and broader validation requested by the owning worker or Gauge.
- Keep commits small, coherent, and named with clean imperative subjects.
- Create GitLab MRs only when the user has authorized MR creation and the token/tooling is available.
- Enable squash, source-branch deletion, and auto-merge through `make create-auto-merge-mr` when authorized.

### Scribe - Context And Documentation Steward

Owns compact handoffs, context defragmentation, durable doc routing, and stale-context cleanup.

Phase 1 responsibilities:

- Keep [context-handoff.md](context-handoff.md) short, current, and limited to durable 1-2 line facts.
- Run `make defragment-context` when a worker or orchestrator identifies context pressure.
- Move lasting decisions into the appropriate durable document rather than appending to the handoff forever.
- Remove stale branch-specific bullets after MRs merge or are abandoned.
- Tell the orchestrator when a context-heavy worker should be closed and replaced by a fresh worker.

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
- Make CI call shared scripts from [scripts/ci](../../scripts/ci).

## Collaboration Rules

- Atlas reviews architecture-affecting changes before implementation agents split boundaries.
- Loom owns the first working checkout path; Hammer waits for a clear extraction reason.
- Forge owns host-vs-container decisions and keeps local startup reproducible.
- Beacon and Gauge define observability and SLO evidence together.
- Shield can block changes that leak secrets, weaken tenant isolation, or add unsafe auth/payment assumptions.
