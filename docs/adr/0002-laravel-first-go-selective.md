# ADR 0002: Laravel First, Go Selective

## Status

Accepted

## Decision

Use Laravel/PHP for checkout orchestration, Blade UI, validation, tenant-aware application services, and Eloquent persistence. Use Go selectively for internal services/processors where concurrency, async work, or tight latency justify it.

## Consequences

- Phase 1 can deliver a working checkout faster.
- Domain-heavy logic stays close to Laravel and Eloquent.
- Go service extraction waits until boundaries are measurable and useful.
- The demo remains close to public SCAYLE hiring/stack signals without overfitting to raw runtime performance.
