# Agent Guidance

Start with [docs/agent/README.md](docs/agent/README.md) for compact agent
context. Use
[docs/human/planning/checkout-mvp-plan.md](docs/human/planning/checkout-mvp-plan.md)
for long-form planning background unless the user explicitly changes direction.

`codex-workflows` is installed as a repo-local Codex tooling layer under
`.agents/skills` and `.codex/agents`. Its recipe skills may be used for
structured planning, TDD implementation, review, diagnosis, and reverse
engineering, but this file and the `docs/agent` operating model remain the
authority when instructions differ.

## Scope Boundaries

- Phase 0 is scaffolding, documentation, guardrails, CI placeholders, and local tooling only.
- Phase 1 defines repository foundation, contracts, named project agents,
  local tooling, and the first Laravel app structure.
- Do not implement full checkout behavior, payment integrations, real auth,
  or AWS deployment in Phase 0.
- Keep local/dev mode free or near-free.
- Keep deploy mode optional and manually approved.
- Use [docs/agent/agents.md](docs/agent/agents.md) for named project-agent
  ownership and handoff boundaries.

## Agent Execution Strategy

- Treat the active Cursor/Codex session agent as the single
  `lead-orchestrator` for this repository unless the user explicitly assigns
  that role to another active session.
- The user does not need to restate that the active agent is the
  `lead-orchestrator`; agents must infer it from this file at session start.
- There must be exactly one active `lead-orchestrator`. If an agent cannot
  determine who owns orchestration, it must stop after read-only status
  collection and ask for clarification before assigning workers, editing files,
  committing, pushing, or preparing merge requests.
- Codex is not allowed to run production-adjacent work in this repo without an
  active `lead-orchestrator` because worker routing, collision prevention,
  stop-condition handling, and integration ownership depend on that role.
- Use the `lead-orchestrator` only for coordination: current intent, task
  slicing, worker assignment, ownership boundaries, branch/worktree routing,
  stop-condition escalation, context defragmentation, and user-facing status.
- Use named worker agents for implementation, docs, validation, integration,
  commits, pushes, and merge request preparation.
- Use Relay for branch integration, validation coordination, commits, pushes,
  and GitLab merge request preparation.
- Use Scribe for compact context handoff, stale-context cleanup, and `make defragment-context`.
- Use specialist agents for bounded work packages with explicit allowed paths,
  out-of-scope paths, acceptance criteria, and validation commands.
- Create background specialist tasks for independent streams so investigation,
  review, docs, tests, and isolated implementation can progress in parallel and
  unblock problems faster.
- Prefer read-only advisory agents for architecture, security, contract, and
  unfamiliar-code reviews.
- Allow implementation agents to edit only inside their assigned path list.
- Do not let parallel agents edit the same files at the same time.
- Follow existing coding standards before touching code. When introducing a new
  implementation technology, add or update a coding standards document before
  writing substantial code in that technology.
- Follow agile slices: BDD scenario, failing Pest test where applicable,
  smallest implementation, validation, then integration.
- If specialist agents are idle, use them for read-only review, backlog
  refinement, fixture design, security/observability review, or CI hardening in
  their own lane.
- Stop and ask before dependency changes, destructive Git operations, paid cloud
  actions, secret handling, or changes outside the assigned scope.
- Prefer small, sharp commits that each capture one coherent behavior,
  guardrail, runtime, or documentation change. When the user has authorized a
  commit/push workflow, Relay commits completed validated slices automatically
  instead of batching unrelated work.
- Commits and pushes happen only when the user asks or when the user has
  explicitly authorized an automatic commit loop for the current branch. Merge
  request creation happens only when the user asks or has enabled agent MR
  creation. Final merge remains manual.

## Repository Rules

- GitLab is primary. GitHub is a mirror.
- Use short-lived branches: `feature/*`, `fix/*`, `docs/*`, `experiment/*`,
  and `agent/*`.
- The `lead-orchestrator` should not implement work directly. Assign direct
  implementation to a named worker on a short-scoped branch, for example
  `agent/outbox-publisher` or `docs/git-workflow`.
- Parallel specialist agents may create their own `agent/<short-scope>`
  branches, for example `agent/outbox-publisher`, when their work is independent
  and would otherwise collide with the orchestrator branch.
- Agent branches must stay narrow, push only to GitLab `origin`, and be
  integrated by Relay through review, cherry-pick, merge, or a GitLab merge
  request under orchestrator direction.
- Push only to GitLab `origin`.
- Agents may create GitLab merge requests targeting `main` when the user asks
  and an approved tool/token is available. Do not merge automatically.
- See [docs/agent/mirroring.md](docs/agent/mirroring.md) for the full workflow.

## Commit Messages

- Use industry-standard imperative subject lines, ideally 50 characters or fewer
  and never padded with tool names.
- Make the subject describe the exact outcome, for example
  `Add Markdown link validation` or `Fix checkout state conflict response`.
- Use a body only when it explains motivation, tradeoffs, validation, or follow-up risk.
- Prefer verbs like `Add`, `Update`, `Fix`, `Document`, `Refine`, `Remove`,
  `Align`, `Enable`, or `Harden`.
- Do not use generic tool-generated messages such as `Added by cursor`.
- Do not mention vendor inspiration or competitor names in commit messages.
- Keep commits small and coherent; split unrelated docs, runtime, test,
  and implementation changes unless a single atomic slice requires them together.

## Merge Requests

- Target GitLab `main` unless the user names another target branch.
- Use concise MR titles that can also serve as the squash commit subject.
- Fill the MR description with summary, validation, risk, and any manual follow-up.
- Enable squash-on-merge where possible and provide a clean squash commit message.
- Ask the user to review and merge; do not merge automatically.

## Secrets

- Never commit secrets, credentials, private keys, tokens, `.env`, Terraform
  state, or generated kubeconfigs.
- The `cursor-dev-agent-git` token is only for local Git over HTTPS and must not
  be used for CI, API automation, registry pushes, or deploys.
- Keep placeholders in `.env.example`; real values stay outside the repository.

## Architecture Principles

- Target PHP 8.5 for Laravel application code and follow
  [docs/agent/coding-standards/php-8.5.md](docs/agent/coding-standards/php-8.5.md).
- Keep the checkout app as a Laravel modular monolith with clean boundaries; see
  [docs/human/adr/0006-laravel-clean-boundaries.md](docs/human/adr/0006-laravel-clean-boundaries.md).
- Laravel/PHP owns checkout orchestration, Blade UI, public REST APIs,
  validation, persistence, and the first complete happy path.
- Go is introduced only for selected processors or services with clear
  concurrency, async, or latency value.
- Do not split pricing, catalog, payment, order, and inventory into separate
  services before the Laravel happy path is stable.
- Blade views receive explicit view models and must not query databases directly.
- Public tenant access must not rely on an untrusted path segment or plain `X-Tenant-Id` header.

## Data And Consistency

- MySQL is the source of truth for tenant, checkout, order, inventory, and identity data.
- Redis is for cache, short-lived locks, idempotency, rate limits, sessions,
  and Redis Streams in local mode.
- OpenSearch is a projection/read model, never a transactional dependency for checkout.
- Checkout/order writes require ACID transactions and idempotency.
- Async side effects must not decide whether an order exists.

## Observability And Errors

- OpenTelemetry/OTLP is the default telemetry contract.
- Grafana Cloud is the preferred managed backend later; Datadog remains optional.
- Public HTTP APIs should use RFC 9457 Problem Details.
- Logs, metrics, and traces should include request ID, trace ID, tenant, route, status, and latency.

## Testing Expectations

- Use BDD for user-facing behavior and TDD for implementation.
- Add focused tests with each implementation phase.
- Tenant isolation and checkout idempotency require integration coverage before
  the checkout path is considered complete.
- CI scripts should stay shared under [scripts/ci](scripts/ci).
