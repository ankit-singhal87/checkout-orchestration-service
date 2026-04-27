# Known Gaps

## Purpose

This page reduces overclaim risk by listing what is incomplete, simulated, or intentionally deferred.

## Status

The project is an active work-in-progress. It has a runnable local checkout path and useful validation evidence, but several production, integration, and polish areas are not complete.

## Key points

- Do not treat the repository as ready for production use.
- The demo proves architecture and local behavior more than operational maturity.
- Payment, inventory, identity, observability, and AWS deployment need further implementation.
- HTTP/3 coverage is limited to Caddy edge smoke checks.

## Details

| Area | Current state | Gap | Next step |
| --- | --- | --- | --- |
| UI polish | Blade checkout screens support the demo flow | Visual design and accessibility polish are limited | Refine view models, styling, validation display, and responsive behavior |
| Real PSP integration | Payment authorization is simulated | No real payment-service-provider integration | Add a sandbox adapter only after boundaries and secret handling are ready |
| Real inventory service | Inventory reservation is simulated/local | No standalone inventory service owns stock holds yet | Introduce the planned service boundary with idempotent reservation contracts |
| Keycloak/OIDC | Optional identity profile exists as infrastructure direction | Checkout does not use real Keycloak/OIDC integration yet | Add optional login/account linking without blocking guest checkout |
| AWS deployment | AWS-oriented target is documented | Infrastructure is not yet provisioned or operated | Add budget guardrails, tags, destroy runbooks, and manual approval gates before apply |
| Production image hardening | Local images support development and smoke checks | Runtime image hardening remains future work | Add production image review, non-root runtime checks, and dependency hygiene |
| Observability | Request IDs, trace IDs, and JSON logs exist where implemented | Full application OTLP traces, metrics, dashboards, and exporter profiles are incomplete | Wire instrumentation behind the OTLP boundary and keep backend choice explicit |
| HTTP/3 testing | Caddy parity smoke validates edge negotiation | No full H3 matrix or Laravel-level H3 termination | Keep H3 at the edge profile and document any future matrix separately |
| Broad commerce APIs | Core shop/cart/checkout flow exists | Vouchers, collection points, loyalty, address book, and broad customer account flows are deferred | Add only after the checkout core and service boundaries are stable |
| OpenSearch read model | Search/profile infrastructure is optional | OpenSearch is not transactional checkout infrastructure | Keep MySQL as the source of truth and add projections asynchronously |
| Project maturity | Local checkout orchestration and async scaffolding are demonstrable | The project remains WIP | Keep claims tied to validation evidence and ADRs |

## Current limitations

The gaps above are intentional review boundaries. They should be read as implementation backlog, not hidden production capability.

## Source anchors

- [Project overview](../../README.md)
- [Architecture summary](architecture.md)
- [Tradeoff summary](tradeoff-summary.md)
- [MVP planning background](planning/checkout-mvp-plan.md)
- [Risk register](phase-0-risk-register.md)
- [Observability decision](adr/0003-observability-otel-first.md)
- [Checkout MVP pivot](adr/0008-checkout-mvp-architecture-pivot.md)

## Where to go from here

Use the [architecture summary](architecture.md) to see current boundaries, the [tradeoff summary](tradeoff-summary.md) to understand decision rationale, and the [MVP planning background](planning/checkout-mvp-plan.md) for longer-term backlog context.
