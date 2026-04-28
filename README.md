# Checkout Orchestration Service

Independent, work-in-progress, generic headless-commerce checkout orchestration POC. The repository demonstrates a local-first Laravel checkout application with optional parity/runtime profiles and AWS-oriented deployment planning kept separate from the default developer loop.

## Status

- WIP architecture POC.
- Not suitable for production use.
- Independent generic headless-commerce checkout demo.
- Independent project with no vendor affiliation, sponsorship, or proprietary implementation basis.

See [DISCLAIMER.md](DISCLAIMER.md) for the full independent-project disclaimer.

## Quick Review

Start with the [human reviewer guide](wiki/review/reviewer-guide.md) for a 10-minute path through project status, architecture decisions, checkout orchestration, parity checks, and command entrypoints.

## Quickstart

```bash
make check-tools
cp .env.example .env
make up
make up-app
```

The default checkout app listens on `http://localhost:8080`. Tenant demo hosts include `http://fashion-demo.localhost:8080/shop` and `http://sports-demo.localhost:8080/shop`.

## Execution Modes

- Local default: Nginx/PHP-FPM over HTTP for fast Laravel development.
- Parity: Caddy HTTPS/H1/H2/H3 edge profile for local edge smoke checks.
- Optional runtime: RoadRunner/Octane for performance/runtime experiments.
- Local/CI database: MySQL container.
- Production target: RDS MySQL through AWS-oriented deployment planning.

## Documentation

- [Human docs](wiki/README.md) - reviewer-facing architecture, ADRs, and planning context.
- [Automation guide](docs/automation/README.md) - low-token operating context for agents and local automation.
- [Documentation map](docs/README.md) - short audience-based index.

## Known Gaps

- UI is not polished.
- No real PSP integration.
- No real inventory service.
- No real Keycloak/OIDC integration yet.
- AWS deployment is documented but not yet provisioned.
- Production image hardening is future work.
