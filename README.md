# Checkout Orchestration Service

Multi-tenant SaaS checkout demo inspired by public headless checkout concepts. The project starts with a local-first Laravel checkout orchestration app and keeps AWS/EKS deployment assets optional until budget guardrails and destroy runbooks exist.

## Current Phase

This repository is entering Phase 2: local runnable checkout breadth on top of the completed Phase 1 foundation. Implementation stays Laravel-first and local-first; Go workers, RoadRunner, Redis Streams, OpenSearch projections, and cloud deploy assets remain opt-in slices.

## Target Shape

- Laravel owns checkout orchestration, Blade UI, public REST APIs, validation, persistence, and the first happy path.
- Go is reserved for selected internal processors or services after the Laravel path is working.
- Local/dev mode runs free or near-free with Docker Compose.
- Deploy mode is optional and targets AWS through Terraform and Kubernetes assets later.
- GitLab is primary. GitHub is a mirror.

## Local Quickstart

```bash
make check-tools
cp .env.example .env
make up
```

By default, Docker Compose starts supporting infrastructure. Start the Laravel checkout container with:

```bash
make up-app
```

By default, local infrastructure is limited to MySQL and Redis. Optional search, observability, and identity services stay behind Compose profiles.

```bash
make up-search
make up-observability
make up-identity
```

The checkout app listens on `http://localhost:8080` by default and resolves tenants by host. Use `http://fashion-demo.localhost:8080/shop` or `http://sports-demo.localhost:8080/shop`. Go workers are added in later phases.

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
