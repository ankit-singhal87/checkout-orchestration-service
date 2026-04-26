# Agent Guidance

Follow `docs/planning/checkout-mvp-plan.md` unless the user explicitly changes direction.

## Scope Boundaries

- Phase 0 is scaffolding, documentation, guardrails, CI placeholders, and local tooling only.
- Phase 1 defines repository foundation, contracts, named project agents, local tooling, and the first Laravel app structure.
- Do not implement full checkout behavior, payment integrations, real auth, or AWS deployment in Phase 0.
- Keep local/dev mode free or near-free.
- Keep deploy mode optional and manually approved.
- Use `docs/agents.md` for named project-agent ownership and handoff boundaries.

## Repository Rules

- GitLab is the primary repository and CI/CD host.
- GitHub is a public read-only mirror for validation and discoverability.
- Do not make GitHub Actions responsible for deployments or releases.
- Use short-lived branches: `feature/*`, `fix/*`, `docs/*`, and `experiment/*`.
- Do not create merge requests automatically until a separate GitLab API token exists.

## Secrets

- Never commit secrets, credentials, private keys, tokens, `.env`, Terraform state, or generated kubeconfigs.
- The `cursor-dev-agent-git` token is only for local Git over HTTPS and must not be used for CI, API automation, registry pushes, or deploys.
- Keep placeholders in `.env.example`; real values stay outside the repository.

## Architecture Principles

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

- Add focused tests with each implementation phase.
- Tenant isolation and checkout idempotency require integration coverage before the checkout path is considered complete.
- CI scripts should stay shared under `scripts/ci` so GitLab and GitHub call the same checks where practical.