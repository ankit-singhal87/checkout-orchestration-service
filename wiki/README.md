# Human Review Docs

## Purpose

This is the human-facing entrypoint for the checkout orchestration service. It is written for reviewers who need a concise view of what the project is, what works today, which decisions matter, and where the gaps are.

## Status

The project is a work-in-progress, independent, vendor-neutral checkout orchestration proof of concept for a generic headless-commerce platform. It is not suitable for production use.

Local development is the primary mode. AWS-oriented deployment planning exists, but cloud deployment remains optional and manually approved.

## Key points

- Start with the [reviewer guide](review/reviewer-guide.md) for the shortest path.
- Use the [architecture summary](architecture/summary.md) for runtime, checkout, worker, and protocol boundaries.
- Use the [tradeoff summary](review/tradeoff-summary.md) for the highest-signal decisions.
- Use the [known gaps](status/known-gaps.md) page to avoid over-reading current maturity.
- Agent-operational documentation lives in [agent docs](../docs/agent/README.md) and is not required for human review.

## Details

| Need | Read |
| --- | --- |
| 10-minute review | [Reviewer guide](review/reviewer-guide.md) |
| System and runtime shape | [Architecture summary](architecture/summary.md) |
| Decision rationale | [Tradeoff summary](review/tradeoff-summary.md) |
| Current limitations | [Known gaps](status/known-gaps.md) |
| Deeper decision records | [Architecture decisions](adr/README.md) |
| Older C4 supporting views | [Architecture evidence](architecture/README.md) |
| Longer roadmap context | [MVP planning background](roadmap/checkout-mvp-plan.md) |

## Current limitations

- The UI demonstrates flow, not polish.
- Payment, inventory, identity, observability, and AWS deployment are incomplete or simulated.
- Runtime parity checks are local smoke checks, not proof of production readiness.

## Source anchors

- [Project overview](../README.md)
- [Architecture decisions](adr/README.md)
- [Phase 1 foundation summary](status/phase-1-foundation.md)
- [Risk register](status/phase-0-risk-register.md)

## Where to go from here

Read the [reviewer guide](review/reviewer-guide.md), then jump to the [architecture summary](architecture/summary.md) or [tradeoff summary](review/tradeoff-summary.md) depending on whether you want system shape or decision rationale first.
