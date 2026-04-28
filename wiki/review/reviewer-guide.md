# Reviewer Guide

## Purpose

This guide gives an external reviewer a compact path through the repository without requiring agent-operation context.

## Status

The project is a work-in-progress architecture proof of concept for a multi-tenant commerce checkout platform. It currently demonstrates a local Laravel checkout path, runtime profiles, CI checks, and documented architecture tradeoffs. It is not suitable for production use.

## Key points

- Main point: the repository is a reviewable checkout orchestration demo with explicit boundaries, tests, ADRs, and known gaps.
- Default local runtime is Laravel behind Nginx/PHP-FPM over HTTP.
- Optional profiles cover RoadRunner/Octane runtime smoke checks and Caddy HTTPS/H1/H2/H3 edge smoke checks.
- Order confirmation uses MySQL transactions, idempotency, and a transactional outbox row.
- Redis Streams publication and a local Laravel order processor path exist for demos; they are scaffold evidence while the Phase 3 target moves toward Go inventory and order-preprocessor boundaries.

## Details

### 10-minute review path

1. Read the [project overview](../../README.md).
2. Skim the [architecture summary](../architecture/summary.md).
3. Review the [Laravel boundary decision](../../docs/adr/ADR-0006-laravel-clean-boundaries.md).
4. Review the [RDS MySQL decision](../../docs/adr/ADR-0007-production-database-rds-mysql.md).
5. Inspect the [checkout orchestration implementation](../../apps/checkout/app/Application/Checkout/CheckoutManager.php).
6. Skim the [local command entrypoints](../../Makefile).

### 30-minute review path

1. Follow the 10-minute path.
2. Read the [tradeoff summary](tradeoff-summary.md).
3. Read the [known gaps](../status/known-gaps.md).
4. Inspect the [edge parity smoke check](../../scripts/test/checkout-parity.sh).
5. Inspect the [outbox publisher command](../../apps/checkout/app/Console/Commands/PublishOutboxEvents.php).
6. Inspect the [order processor command](../../apps/checkout/app/Console/Commands/ConsumeOrderConfirmedEvents.php).
7. Skim the [older C4 supporting views](../architecture/README.md) only where more diagram evidence helps.

### What works today

| Area | Current evidence |
| --- | --- |
| Tenant-aware storefront | Demo tenant hosts route to shop/cart/checkout flows. |
| Guest checkout | Address, shipping, payment selection, and confirmation are implemented for the local happy path. |
| Idempotent order confirmation | Confirmation is guarded by tenant and idempotency key inside a MySQL transaction. |
| Transactional outbox | Successful confirmation writes a committed `order.confirmed` outbox row. |
| Async local path | Outbox rows can publish to Redis Streams and be consumed by a replay-safe Laravel scaffold order processor. |
| Runtime checks | CI includes checkout tests, RoadRunner smoke, Caddy parity smoke, and selected worker smoke checks. |
| Public API errors | API failures target RFC 9457 Problem Details with safe context where implemented. |

### Suggested demo narrative

Start with the project status in the root overview. Show the default local runtime, then walk through a tenant checkout. Use the checkout manager to explain the current Laravel transaction boundary, then show the outbox publisher and worker command as scaffold async evidence. Close with the Phase 3 pivot and known gaps so the review stays grounded.

### What not to review deeply

- Framework boilerplate, generated vendor code, and UI polish.
- Optional search, identity, observability, and AWS assets unless reviewing future direction.
- Agent routing, branch orchestration, or token-saving notes.
- Full production operations, because deployment is not claimed as complete.

### How to interpret AI-agent-assisted work

Treat the repository like any other codebase: review source, tests, ADRs, and validation evidence. The relevant human signal is whether the architecture is coherent, the boundaries are explicit, the implementation matches the claims, and known gaps are stated plainly.

## Current limitations

- The UI is functional but not polished.
- Payment and inventory behavior are simulated.
- The local order processor consumes `order.confirmed` as scaffold evidence; the target Go order preprocessor and inventory service are not implemented yet.
- Identity integration is not active in the checkout path.
- AWS deployment and production image hardening remain future work.
- HTTP/3 coverage is an edge smoke check, not a full application protocol matrix.
- Broad commerce API features such as vouchers, collection points, loyalty, address book, and full customer account flows are deferred.
- OpenSearch/search projections are not transactional checkout infrastructure.

## Source anchors

- [Project overview](../../README.md)
- [Architecture summary](../architecture/summary.md)
- [Tradeoff summary](tradeoff-summary.md)
- [Known gaps](../status/known-gaps.md)
- [Checkout orchestration implementation](../../apps/checkout/app/Application/Checkout/CheckoutManager.php)
- [Web checkout routes](../../apps/checkout/routes/web.php)
- [Public checkout API routes](../../apps/checkout/routes/api.php)
- [CI pipeline](../../.gitlab-ci.yml)

## Where to go from here

Use the [architecture summary](../architecture/summary.md) for system shape, the [tradeoff summary](tradeoff-summary.md) for decision rationale, and the [known gaps](../status/known-gaps.md) before drawing conclusions about maturity.
