# Checkout Orchestration Service

Multi-tenant SaaS checkout demo inspired by public headless checkout concepts. The project starts with a local-first Laravel checkout orchestration app and keeps AWS/EKS deployment assets optional until budget guardrails and destroy runbooks exist.

## Current Phase

This repository is in Phase 0: scaffolding, risk guardrails, local tooling, and CI placeholders. Do not build the full checkout implementation in this phase.

## Target Shape

- Laravel owns checkout orchestration, Blade UI, public REST APIs, validation, persistence, and the first happy path.
- Go is reserved for selected internal processors or services after the Laravel path is working.
- Local/dev mode runs free or near-free with Docker Compose.
- Deploy mode is optional and targets AWS through Terraform and Kubernetes assets later.
- GitLab is the primary source of truth. GitHub is a read-only mirror.

## Local Quickstart

```bash
sh scripts/dev/check-tools.sh
cp .env.example .env
sh scripts/dev/up.sh
```

The Phase 0 Docker Compose file starts supporting infrastructure only. The Laravel app and Go workers are added in later phases.

## Useful Docs

- `docs/planning/checkout-mvp-plan.md` - full MVP plan and implementation phases
- `docs/phase-0-risk-register.md` - risks and mitigations
- `docs/architecture/README.md` - C4 architecture documentation index
- `docs/adr/README.md` - architecture decision records
- `docs/branching-strategy.md` - branch and merge request conventions
- `docs/mirroring.md` - GitLab to GitHub mirror expectations
- `docs/local-tools.md` - local toolchain notes
- `docs/debugging.md` - debugging guide

## CI

GitLab CI is primary and runs `scripts/ci/validate-scaffold.sh`. GitHub Actions is mirror validation only and must not deploy.