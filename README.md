# Checkout Orchestration Service

Multi-tenant SaaS checkout demo inspired by public headless checkout concepts. The project starts with a local-first Laravel checkout orchestration app and keeps AWS/EKS deployment assets optional until budget guardrails and destroy runbooks exist.

## Current Phase

This repository is in Phase 1: repository foundation, contracts, named project agents, and local tooling. The first checkout implementation should stay Laravel-first and local-first.

## Target Shape

- Laravel owns checkout orchestration, Blade UI, public REST APIs, validation, persistence, and the first happy path.
- Go is reserved for selected internal processors or services after the Laravel path is working.
- Local/dev mode runs free or near-free with Docker Compose.
- Deploy mode is optional and targets AWS through Terraform and Kubernetes assets later.
- GitLab is primary. GitHub is a mirror.

## Local Quickstart

```bash
sh scripts/dev/check-tools.sh
cp .env.example .env
sh scripts/dev/up.sh
```

By default, Docker Compose starts supporting infrastructure. Start the Laravel checkout container with:

```bash
COMPOSE_PROFILES=app sh scripts/dev/up.sh
```

The checkout app skeleton exists and listens on `http://localhost:8080` by default. Go workers are added in later phases.

Bootstrap is idempotent and exits when `apps/checkout/artisan` already exists:

```bash
sh scripts/dev/bootstrap-checkout-app.sh
```

## Useful Docs

- `docs/planning/checkout-mvp-plan.md` - full MVP plan and implementation phases
- `docs/phase-1-foundation.md` - Phase 1 work streams and acceptance criteria
- `docs/agents.md` - named project-agent roles and ownership boundaries
- `docs/contracts` - tenant, checkout state, error, event, and seed data contracts
- `docs/api/openapi.checkout.yaml` - initial checkout API contract
- `apps/checkout/docs` - Laravel app conventions and route surface
- `docs/contracts/bdd-tdd.md` - BDD/TDD workflow for implementation
- `docs/coding-standards/php-8.5.md` - PHP 8.5 coding standards
- `docs/phase-0-risk-register.md` - risks and mitigations
- `docs/architecture/README.md` - C4 architecture documentation index
- `docs/adr/README.md` - architecture decision records
- `docs/branching-strategy.md` - branch and merge request conventions
- `docs/mirroring.md` - GitLab to GitHub mirror expectations
- `docs/local-tools.md` - local toolchain notes
- `docs/debugging.md` - debugging guide

## CI

GitLab CI is primary and runs `scripts/ci/validate-scaffold.sh` and `scripts/ci/validate-phase1.sh`. GitHub Actions is mirror validation only and must not deploy.

## Repository Workflow

Push to GitLab `origin` only. Create and merge MRs manually in GitLab. See `docs/mirroring.md`.