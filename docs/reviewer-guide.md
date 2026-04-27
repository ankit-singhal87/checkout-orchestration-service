# Reviewer Guide

## Status

This is a work-in-progress architecture POC for an independent generic headless-commerce checkout platform demo. It is not production-ready and is not affiliated with, endorsed by, sponsored by, or based on any vendor's internal implementation.

## Main Point

This project demonstrates AI-agent-assisted engineering with explicit architectural control: scoped workstreams, ADRs, tests, CI checks, parity profiles, and reviewable tradeoffs.

## 10-Minute Review Path

1. [../README.md](../README.md) - project intent, status, local mode, and known gaps.
2. [human/adr/0006-laravel-clean-boundaries.md](human/adr/0006-laravel-clean-boundaries.md) - why Laravel owns checkout orchestration and UI boundaries.
3. [human/adr/0007-production-database-rds-mysql.md](human/adr/0007-production-database-rds-mysql.md) - production RDS MySQL decision versus local/CI MySQL containers.
4. [../apps/checkout/app/Application/Checkout/CheckoutManager.php](../apps/checkout/app/Application/Checkout/CheckoutManager.php) - checkout orchestration flow.
5. [../scripts/test/checkout-parity.sh](../scripts/test/checkout-parity.sh) - Caddy edge parity smoke checks.
6. [../Makefile](../Makefile) - local, CI, and parity execution entrypoints.

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

## Environment Notes

Default local development uses HTTP on localhost through Nginx/PHP-FPM for speed and debugging. HTTPS/H1/H2/H3 edge parity is a separate Caddy profile, and HTTP/3/QUIC coverage is edge smoke testing only. Production database planning targets RDS MySQL; local and CI use MySQL containers. RoadRunner/Octane is optional for performance and parity checks, not the mandatory baseline runtime.
