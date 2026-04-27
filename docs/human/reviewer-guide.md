# Reviewer Guide

## Status

This is a work-in-progress architecture POC for an independent generic headless-commerce checkout orchestration demo. It is not production-ready and is not affiliated with, endorsed by, sponsored by, or based on any vendor internal implementation.

## Main Point

The project shows AI-agent-assisted engineering under explicit architectural control: scoped workstreams, ADRs, tests, CI checks, runtime parity profiles, and reviewable tradeoffs.

## 10-Minute Review Path

1. [../../README.md](../../README.md) - project purpose, status, quickstart, execution modes, and known gaps.
2. [architecture/README.md](architecture/README.md) - architecture map and C4 views.
3. [adr/0006-laravel-clean-boundaries.md](adr/0006-laravel-clean-boundaries.md) - why Laravel owns checkout orchestration and UI boundaries.
4. [adr/0007-production-database-rds-mysql.md](adr/0007-production-database-rds-mysql.md) - production RDS MySQL decision versus local/CI MySQL containers.
5. [../../apps/checkout/app/Application/Checkout/CheckoutManager.php](../../apps/checkout/app/Application/Checkout/CheckoutManager.php) - checkout orchestration flow.
6. [../../scripts/test/checkout-parity.sh](../../scripts/test/checkout-parity.sh) - Caddy edge parity smoke checks.
7. [../../Makefile](../../Makefile) - local, CI, and parity execution entrypoints.

## What Works Today

- Local Nginx/PHP-FPM Laravel runtime over HTTP on localhost for fast debugging.
- Tenant-aware shop, cart, and checkout flow.
- Order confirmation with idempotency.
- Transactional outbox write.
- Redis Stream publication and consumer path.
- Caddy edge parity smoke checks for HTTPS, HTTP/1.1, HTTP/2, and HTTP/3/QUIC.
- GitLab CI validation.

## Known Gaps

- UI is not polished.
- No real PSP integration.
- No real inventory service.
- No real Keycloak/OIDC integration yet.
- AWS deployment is documented but not fully provisioned.
- Production image hardening is future work.

## Suggested Demo Narrative

Show the root README first to frame the status and boundaries. Then walk through the Laravel-first architecture decision, the RDS MySQL production database decision, the checkout manager flow, and the parity script that exercises the Caddy edge path. Close with the Makefile to show how local and CI commands stay aligned.

## What Not To Review Deeply Unless Needed

- Generated framework boilerplate and vendor directories.
- Optional search, identity, observability, and AWS deployment assets outside the checkout path.
- Agent orchestration notes unless reviewing how work is delegated.
- UI polish; the interface is intentionally not production-ready.

## Environment Notes

Default local development uses HTTP on localhost through Nginx/PHP-FPM for speed and debugging. HTTPS/H1/H2/H3 edge parity is a separate Caddy profile, and HTTP/3/QUIC coverage is edge smoke testing only. Production database planning targets RDS MySQL; local and CI use MySQL containers. RoadRunner/Octane is optional for performance and parity checks, not the mandatory baseline runtime.
