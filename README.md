# Checkout Orchestration Service

Independent generic headless-commerce checkout platform demo. The project starts with a local-first Laravel checkout orchestration app and keeps AWS-oriented production deployment assets optional until budget guardrails and destroy runbooks exist.

## Status

- Work-in-progress architecture POC.
- Independent generic headless-commerce checkout demo.
- Not production-ready.
- Not affiliated with, endorsed by, sponsored by, or based on any vendor's internal implementation.

## Disclaimer

This is an independent learning and architecture demonstration project for a generic headless-commerce checkout platform. It is not affiliated with, endorsed by, sponsored by, or based on any vendor's internal implementation. No claim of compatibility, certification, partnership, or endorsement is made. All third-party names, trademarks, logos, and product references belong to their respective owners.

See [DISCLAIMER.md](DISCLAIMER.md).

## Reviewer Guide

See [docs/reviewer-guide.md](docs/reviewer-guide.md) for the detailed reviewer path. It includes a 10-minute review flow through the project status, architecture decisions, checkout orchestration, runtime parity checks, and local command entrypoints.

## What Works Today

- Local Nginx/PHP-FPM Laravel runtime over HTTP on localhost for fast debugging.
- Tenant-aware shop/cart/checkout flow.
- Order confirmation with idempotency.
- Transactional outbox write.
- Redis Stream publication/consumer path.
- Caddy edge parity smoke checks for HTTPS, HTTP/1.1, HTTP/2, and HTTP/3/QUIC.
- GitLab CI validation.

## Known Gaps

- UI is not polished.
- No real PSP integration.
- No real inventory service.
- No real Keycloak/OIDC integration yet.
- AWS deployment is documented but not fully provisioned.
- Production image hardening is future work.

## Current Phase

This repository is entering Phase 3: peripheral services and workers on top of the completed Phase 1 foundation and closed Phase 2 local checkout/system-completion baseline. Implementation stays Laravel-first and local-first; the default local stack is Nginx/PHP-FPM over HTTP on localhost for speed and debugging. Caddy owns the separate local HTTPS/H1/H2/H3 edge parity path, and Phase 3 adds async workers around the checkout core before any service extraction. Cloud deploy assets and broad observability/provider work remain Phase 4+.

## Target Shape

- Laravel owns checkout orchestration, Blade UI, public REST APIs, validation, persistence, and the first happy path.
- Go is reserved for selected internal processors or services after the Laravel path is working.
- Local/dev mode runs free or near-free with Docker Compose.
- Deploy mode is optional and targets AWS through Terraform and Kubernetes assets later, with RDS MySQL as the production database shape while local/CI use MySQL containers.
- GitLab is primary. GitHub is a mirror.

## Local Quickstart

```bash
make check-tools
cp .env.example .env
make up
```

By default, Docker Compose starts supporting infrastructure. Start the Laravel checkout stack with Nginx and PHP-FPM over plain HTTP/1.1 for fast local feedback:

```bash
make up-app
```

Use RoadRunner/Octane only when testing the optional performance/parity runtime profile; it is not the mandatory baseline:

```bash
make up-roadrunner
```

For local-production parity, start the Caddy edge path:

```bash
make up-parity
```

The parity path uses Caddy in front of the default Nginx/PHP-FPM stack with local HTTPS, HTTP/1.1, HTTP/2, HTTP/3 over QUIC/UDP 443, forwarded headers, security headers, and request-size limits. Caddy uses its local internal CA, so use `curl -k` for command-line checks unless you trust the local CA. HTTP/3/QUIC coverage here is edge smoke testing only, not a full application protocol test matrix.

The direct Compose form is also supported:

```bash
docker compose -f docker-compose.yml -f docker-compose.caddy.yml up
```

Do not use the parity proxy for every TDD loop; keep the default path fast. Future gRPC endpoints must use HTTP/2 even in the fast path.

By default, local infrastructure is limited to MySQL and Redis. Optional search, observability, and identity services stay behind Compose profiles.

```bash
make up-search
make up-observability
make up-identity
```

The default checkout app listens on `http://localhost:8080` and resolves tenants by host. Use `http://fashion-demo.localhost:8080/shop` or `http://sports-demo.localhost:8080/shop`.

The Caddy edge listens on `https://localhost:8443`. Use `https://fashion-demo.localhost:8443/shop` or `https://sports-demo.localhost:8443/shop`.

Outbox publication is available as a manual local async boundary:

```bash
make demo-outbox-publish
make demo-redis-events
```

See [docs/agent/demo-runbook.md](docs/agent/demo-runbook.md) for the closed Phase 2 demo flow. Phase 3 adds peripheral workers and services around that baseline.

Bootstrap is idempotent and exits when [apps/checkout/artisan](apps/checkout/artisan) already exists:

```bash
make bootstrap-checkout
```

## Useful Docs

- [docs/README.md](docs/README.md) - documentation map by audience
- [docs/agent/README.md](docs/agent/README.md) - compact agent-readable operating context
- [docs/human/README.md](docs/human/README.md) - human-readable architecture and planning index
- [docs/human/planning/checkout-mvp-plan.md](docs/human/planning/checkout-mvp-plan.md) - full MVP plan and implementation phases
- [docs/human/phase-1-foundation.md](docs/human/phase-1-foundation.md) - completed Phase 1 work streams and acceptance criteria
- [docs/agent/agents.md](docs/agent/agents.md) - named project-agent roles and ownership boundaries
- [docs/agent/contracts](docs/agent/contracts) - tenant, checkout state, error, event, and seed data contracts
- [docs/agent/api/openapi.checkout.yaml](docs/agent/api/openapi.checkout.yaml) - initial checkout API contract
- [apps/checkout/docs](apps/checkout/docs) - Laravel app conventions and route surface
- [docs/agent/contracts/bdd-tdd.md](docs/agent/contracts/bdd-tdd.md) - BDD/TDD workflow for implementation
- [docs/agent/coding-standards/php-8.5.md](docs/agent/coding-standards/php-8.5.md) - PHP 8.5 coding standards
- [docs/human/phase-0-risk-register.md](docs/human/phase-0-risk-register.md) - risks and mitigations
- [docs/human/architecture/README.md](docs/human/architecture/README.md) - C4 architecture documentation index
- [docs/human/adr/README.md](docs/human/adr/README.md) - architecture decision records
- [docs/agent/branching-strategy.md](docs/agent/branching-strategy.md) - branch and merge request conventions
- [docs/agent/mirroring.md](docs/agent/mirroring.md) - GitLab to GitHub mirror expectations
- [docs/agent/local-tools.md](docs/agent/local-tools.md) - local toolchain notes
- [docs/agent/debugging.md](docs/agent/debugging.md) - debugging guide

## CI

GitLab CI is primary and runs [scripts/ci/validate-scaffold.sh](scripts/ci/validate-scaffold.sh) and [scripts/ci/validate-phase1.sh](scripts/ci/validate-phase1.sh). GitHub Actions is mirror validation only and must not deploy.

Use `make help` for the local command index. Make targets are thin wrappers over scripts so CI and agent workflows stay aligned.

For a new local workstation, run `make install-host-tools` to install missing essential tools where supported. Authenticate external CLIs separately, for example with `glab auth login`.

## Repository Workflow

Push to GitLab `origin` only. Create and merge MRs manually in GitLab. See [docs/agent/mirroring.md](docs/agent/mirroring.md).
