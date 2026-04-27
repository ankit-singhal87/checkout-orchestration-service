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

| Decision | Chosen | Alternative | Why | Consequence | Detail |
| --- | --- | --- | --- | --- | --- |
| RDS MySQL vs MySQL on EKS | RDS MySQL for production target | Self-managed MySQL in EKS | Managed database operations reduce scope and risk | EKS remains for application workloads; local/CI containers are substitutes only | [RDS decision](adr/0007-production-database-rds-mysql.md) |
| PHP-FPM baseline vs RoadRunner/Octane default | Nginx/PHP-FPM as default local baseline | RoadRunner/Octane as default | Familiar debugging and fast feedback are more valuable for the default loop | RoadRunner remains an explicit optional profile | [Runtime summary](architecture.md) |
| HTTP local vs HTTPS everywhere | HTTP for local default | Force HTTPS in every local loop | Keeps local development simple while Caddy covers edge parity separately | TLS behavior is reviewed through the parity profile | [Caddy edge configuration](../../infra/local/caddy/Caddyfile) |
| Caddy parity vs default local proxy | Caddy only in parity profile | Caddy as the default proxy | Edge protocol checks should not slow every checkout iteration | H1/H2/H3 and security headers are smoke-tested separately | [Parity smoke check](../../scripts/test/checkout-parity.sh) |
| MySQL container local/CI vs cloud DB in CI | MySQL containers for local and CI | Cloud database in CI | Cheap, repeatable validation without cloud dependency | CI validates behavior but not managed database operations | [CI pipeline](../../.gitlab-ci.yml) |
| H1/H2 + HTTP/3 smoke vs full H3 matrix | Edge smoke for H1, H2, and H3 | Exhaustive protocol matrix | The app does not terminate H3; Caddy edge negotiation is the relevant local signal | No claim of full HTTP/3 application coverage | [Architecture summary](architecture.md) |
| Laravel modular monolith vs microservices | Laravel modular monolith for checkout core | Split every domain into services early | A working checkout path needs cohesive transactions and simple local review | Service extraction waits for useful, measurable boundaries | [Laravel boundary decision](adr/0006-laravel-clean-boundaries.md) |
| Transactional outbox/Redis Streams vs direct synchronous side effects | Transactional outbox plus local Redis Streams | Synchronous downstream side effects in checkout confirmation | Order existence must depend on committed MySQL state, not async dependencies | Local demo delivery is retryable and idempotent; production-grade delivery is future work | [Consistency decision](adr/0004-checkout-consistency-model.md) |
| Agent-assisted implementation with human-readable docs/ADRs | Keep human docs, ADRs, tests, and source as review evidence | Rely on agent notes as the main explanation | Reviewers need durable, concise project documentation | Agent operations stay out of the human review path | [Human review docs](README.md) |

## Current limitations

- Some target-shape decisions are documented ahead of full implementation.
- RoadRunner, Caddy parity, and worker smoke checks are validation slices, not deployment guarantees.
- The future service boundary described in later ADRs is not yet the complete runtime.

## Source anchors

- [Architecture summary](architecture.md)
- [Known gaps](known-gaps.md)
- [Architecture decisions](adr/README.md)
- [Project overview](../../README.md)
- [CI pipeline](../../.gitlab-ci.yml)

## Where to go from here

Read the [architecture summary](architecture.md) for system shape, then use the [architecture decision records](adr/README.md) for deeper rationale and [known gaps](known-gaps.md) for current limitations.
