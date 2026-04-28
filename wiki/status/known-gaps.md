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

- **UI polish**
  - Current state: Blade checkout screens support the demo flow.
  - Gap: Visual design and accessibility polish are limited.
  - Next step: Refine view models, styling, validation display, and responsive behavior.
- **Real PSP integration**
  - Current state: Payment authorization is simulated.
  - Gap: No real payment-service-provider integration.
  - Next step: Add a sandbox adapter only after boundaries and secret handling are ready.
- **Real inventory service**
  - Current state: Inventory reservation is simulated/local.
  - Gap: No standalone inventory service owns stock holds yet.
  - Next step: Introduce the planned service boundary with idempotent reservation contracts.
- **Keycloak/OIDC**
  - Current state: Optional identity profile exists as infrastructure direction.
  - Gap: Checkout does not use real Keycloak/OIDC integration yet.
  - Next step: Add optional login/account linking without blocking guest checkout.
- **AWS deployment**
  - Current state: AWS-oriented target is documented.
  - Gap: Infrastructure is not yet provisioned or operated.
  - Next step: Add budget guardrails, tags, destroy runbooks, and manual approval gates before apply.
- **Production image hardening**
  - Current state: Local images support development and smoke checks.
  - Gap: Runtime image hardening remains future work.
  - Next step: Add production image review, non-root runtime checks, and dependency hygiene.
- **Observability**
  - Current state: Request IDs, trace IDs, and JSON logs exist where implemented.
  - Gap: Full application OTLP traces, metrics, dashboards, and exporter profiles are incomplete.
  - Next step: Wire instrumentation behind the OTLP boundary and keep backend choice explicit.
- **HTTP/3 testing**
  - Current state: Caddy parity smoke validates edge negotiation.
  - Gap: No full H3 matrix or Laravel-level H3 termination.
  - Next step: Keep H3 at the edge profile and document any future matrix separately.
- **Broad commerce APIs**
  - Current state: Core shop/cart/checkout flow exists.
  - Gap: Vouchers, collection points, loyalty, address book, and broad customer account flows are deferred.
  - Next step: Add only after the checkout core and service boundaries are stable.
- **OpenSearch read model**
  - Current state: Search/profile infrastructure is optional.
  - Gap: OpenSearch is not transactional checkout infrastructure.
  - Next step: Keep MySQL as the source of truth and add projections asynchronously.
- **Project maturity**
  - Current state: Local checkout orchestration and async scaffolding are demonstrable.
  - Gap: The project remains WIP.
  - Next step: Keep claims tied to validation evidence and ADRs.

## Current limitations

The gaps above are intentional review boundaries. They should be read as implementation backlog, not hidden production capability.

## Source anchors

- [Project overview](../../README.md)
- [Architecture summary](../architecture/summary.md)
- [Tradeoff summary](../review/tradeoff-summary.md)
- [MVP work plan](../../docs/plans/checkout-mvp-work-plan.md)
- [Risk register](phase-0-risk-register.md)
- [Observability decision](../../docs/adr/ADR-0003-observability-otel-first.md)
- [Checkout MVP pivot](../../docs/adr/ADR-0008-checkout-mvp-architecture-pivot.md)

## Where to go from here

Use the [architecture summary](../architecture/summary.md) to see current boundaries, the [tradeoff summary](../review/tradeoff-summary.md) to understand decision rationale, and the [MVP work plan](../../docs/plans/checkout-mvp-work-plan.md) for longer-term backlog context.
