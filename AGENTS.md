# Agent Guidance

Start with [docs/agent/README.md](docs/agent/README.md) for compact agent context. Use [docs/human/planning/checkout-mvp-plan.md](docs/human/planning/checkout-mvp-plan.md) for long-form planning background unless the user explicitly changes direction.

## Scope Boundaries

- Phase 0 is scaffolding, documentation, guardrails, CI placeholders, and local tooling only.
- Phase 1 defines repository foundation, contracts, named project agents, local tooling, and the first Laravel app structure.
- Do not implement full checkout behavior, payment integrations, real auth, or AWS deployment in Phase 0.
- Keep local/dev mode free or near-free.
- Keep deploy mode optional and manually approved.
- Use [docs/agent/agents.md](docs/agent/agents.md) for named project-agent ownership and handoff boundaries.

## Agent Execution Strategy

- Treat the active Cursor session agent as the lead orchestrator unless the user assigns that role elsewhere.
- Use the lead orchestrator for task slicing, integration, validation, commits, and push requests.
- Use specialist agents for bounded work packages with explicit allowed paths, out-of-scope paths, acceptance criteria, and validation commands.
- Create background specialist tasks for independent streams so investigation, review, docs, tests, and isolated implementation can progress in parallel and unblock problems faster.
- Prefer read-only advisory agents for architecture, security, contract, and unfamiliar-code reviews.
- Allow implementation agents to edit only inside their assigned path list.
- Do not let parallel agents edit the same files at the same time.
- Follow existing coding standards before touching code. When introducing a new implementation technology, add or update a coding standards document before writing substantial code in that technology.
- Follow agile slices: BDD scenario, failing Pest test where applicable, smallest implementation, validation, then integration.
- If specialist agents are idle, use them for read-only review, backlog refinement, fixture design, security/observability review, or CI hardening in their own lane.
- Stop and ask before dependency changes, destructive Git operations, paid cloud actions, secret handling, or changes outside the assigned scope.
- Commits and pushes happen only when the user asks. Merge request creation and merge remain manual.

## Repository Rules

- GitLab is primary. GitHub is a mirror.
- Use short-lived branches: `feature/*`, `fix/*`, `docs/*`, and `experiment/*`.
- Push only to GitLab `origin`.
- Do not create merge requests or merge automatically.
- See [docs/agent/mirroring.md](docs/agent/mirroring.md) for the full workflow.

## Commit Messages

- Use concise messages that describe the change, for example `Document GitLab mirror workflow`.
- Prefer verbs like `Add`, `Update`, `Fix`, `Document`, `Refine`, or `Remove`.
- Do not use generic tool-generated messages such as `Added by cursor`.

## Secrets

- Never commit secrets, credentials, private keys, tokens, `.env`, Terraform state, or generated kubeconfigs.
- The `cursor-dev-agent-git` token is only for local Git over HTTPS and must not be used for CI, API automation, registry pushes, or deploys.
- Keep placeholders in `.env.example`; real values stay outside the repository.

## Architecture Principles

- Target PHP 8.5 for Laravel application code and follow [docs/agent/coding-standards/php-8.5.md](docs/agent/coding-standards/php-8.5.md).
- Keep the checkout app as a Laravel modular monolith with clean boundaries; see [docs/human/adr/0006-laravel-clean-boundaries.md](docs/human/adr/0006-laravel-clean-boundaries.md).
- Laravel/PHP owns checkout orchestration, Blade UI, public REST APIs, validation, persistence, and the first complete happy path.
- Go is introduced only for selected processors or services with clear concurrency, async, or latency value.
- Do not split pricing, catalog, payment, order, and inventory into separate services before the Laravel happy path is stable.
- Blade views receive explicit view models and must not query databases directly.
- Public tenant access must not rely on an untrusted path segment or plain `X-Tenant-Id` header.

## Data And Consistency

- MySQL is the source of truth for tenant, checkout, order, inventory, and identity data.
- Redis is for cache, short-lived locks, idempotency, rate limits, sessions, and Redis Streams in local mode.
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
- Tenant isolation and checkout idempotency require integration coverage before the checkout path is considered complete.
- CI scripts should stay shared under [scripts/ci](scripts/ci).
