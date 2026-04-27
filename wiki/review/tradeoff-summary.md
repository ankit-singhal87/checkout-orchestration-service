# Tradeoff Summary

## Purpose

This page captures the highest-signal architecture choices in one place so reviewers can see what was optimized, what was deferred, and where to inspect deeper rationale.

## Status

These decisions reflect the current work-in-progress checkout demo. Some decisions are implemented locally; others are target-shape decisions for optional future deployment or service extraction.

## Key points

- The project favors a small local feedback loop first.
- Production-adjacent concerns are documented but kept out of the default path.
- Durable checkout/order state stays in MySQL.
- Async side effects leave the request path through an outbox boundary.
- Human-readable ADRs and summaries are part of the review surface.

## Details

### RDS MySQL vs MySQL on EKS

- Chosen: RDS MySQL for production target.
- Alternative: Self-managed MySQL in EKS.
- Why: Managed database operations reduce scope and risk.
- Consequence: EKS remains for application workloads; local/CI containers are substitutes only.
- Detail: [RDS decision](../adr/0007-production-database-rds-mysql.md).

### PHP-FPM baseline vs RoadRunner/Octane default

- Chosen: Nginx/PHP-FPM as default local baseline.
- Alternative: RoadRunner/Octane as default.
- Why: Familiar debugging and fast feedback are more valuable for the default loop.
- Consequence: RoadRunner remains an explicit optional profile.
- Detail: [Runtime summary](../architecture/summary.md).

### HTTP local vs HTTPS everywhere

- Chosen: HTTP for local default.
- Alternative: Force HTTPS in every local loop.
- Why: Keeps local development simple while Caddy covers edge parity separately.
- Consequence: TLS behavior is reviewed through the parity profile.
- Detail: [Caddy edge configuration](../../infra/local/caddy/Caddyfile).

### Caddy parity vs default local proxy

- Chosen: Caddy only in parity profile.
- Alternative: Caddy as the default proxy.
- Why: Edge protocol checks should not slow every checkout iteration.
- Consequence: H1/H2/H3 and security headers are smoke-tested separately.
- Detail: [Parity smoke check](../../scripts/test/checkout-parity.sh).

### MySQL container local/CI vs cloud DB in CI

- Chosen: MySQL containers for local and CI.
- Alternative: Cloud database in CI.
- Why: Cheap, repeatable validation without cloud dependency.
- Consequence: CI validates behavior but not managed database operations.
- Detail: [CI pipeline](../../.gitlab-ci.yml).

### H1/H2 + HTTP/3 smoke vs full H3 matrix

- Chosen: Edge smoke for H1, H2, and H3.
- Alternative: Exhaustive protocol matrix.
- Why: The app does not terminate H3; Caddy edge negotiation is the relevant local signal.
- Consequence: No claim of full HTTP/3 application coverage.
- Detail: [Architecture summary](../architecture/summary.md).

### Laravel modular monolith vs microservices

- Chosen: Laravel modular monolith for checkout core.
- Alternative: Split every domain into services early.
- Why: A working checkout path needs cohesive transactions and simple local review.
- Consequence: Service extraction waits for useful, measurable boundaries.
- Detail: [Laravel boundary decision](../adr/0006-laravel-clean-boundaries.md).

### Transactional outbox/Redis Streams vs direct synchronous side effects

- Chosen: Transactional outbox plus local Redis Streams.
- Alternative: Synchronous downstream side effects in checkout confirmation.
- Why: Order existence must depend on committed MySQL state, not async dependencies.
- Consequence: Local demo delivery is retryable and idempotent; production-grade delivery is future work.
- Detail: [Consistency decision](../adr/0004-checkout-consistency-model.md).

### Agent-assisted implementation with human-readable docs/ADRs

- Chosen: Keep human docs, ADRs, tests, and source as review evidence.
- Alternative: Rely on agent notes as the main explanation.
- Why: Reviewers need durable, concise project documentation.
- Consequence: Agent operations stay out of the human review path.
- Detail: [Human review docs](../README.md).

## Current limitations

- Some target-shape decisions are documented ahead of full implementation.
- RoadRunner, Caddy parity, and worker smoke checks are validation slices, not deployment guarantees.
- The future service boundary described in later ADRs is not yet the complete runtime.

## Source anchors

- [Architecture summary](../architecture/summary.md)
- [Known gaps](../status/known-gaps.md)
- [Architecture decisions](../adr/README.md)
- [Project overview](../../README.md)
- [CI pipeline](../../.gitlab-ci.yml)

## Where to go from here

Read the [architecture summary](../architecture/summary.md) for system shape, then use the [architecture decision records](../adr/README.md) for deeper rationale and [known gaps](../status/known-gaps.md) for current limitations.
