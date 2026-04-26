# BDD And TDD Workflow

Phase 1 and later implementation should use BDD to describe behavior and TDD to drive code.

## BDD First

Start user-facing work by writing behavior scenarios in business language.

Use Gherkin-style phrasing even if the first implementation uses PHPUnit/Pest instead of a dedicated BDD runner:

```gherkin
Feature: Guest checkout

  Scenario: Guest shopper confirms an order
    Given a shopper is browsing the fashion tenant
    And the shopper has an in-stock item in the cart
    When the shopper enters a valid address
    And selects a shipping option
    And selects a simulated payment method
    And confirms the order
    Then an order is created once
    And the shopper sees the confirmation page
```

## TDD Loop

For implementation tasks:

1. Write or update the behavior scenario.
2. Add the smallest failing test that proves the next behavior.
3. Implement the smallest useful code to pass.
4. Refactor while tests stay green.
5. Add integration coverage for tenant isolation, idempotency, and persistence boundaries.

## Executable Test Stack

- Use Pest for Laravel tests.
- Use Pest parallel execution for normal CI and local verification runs.
- Use real MySQL-backed tests for persistence, transaction, locking, constraint, and tenant-scoping behavior.
- Use Faker for deterministic test data variation, with explicit seeds or named factories for reproducibility.
- Use Mockery for external collaborators and hard-to-reach boundaries, not for replacing persistence in integration tests.

## Idempotent Test Data

Executable tests must be repeatable, parallel-safe, and self-cleaning:

- Prefix generated tenants, carts, checkout states, idempotency keys, Redis keys, locks, and outbox rows with a unique test run ID.
- Add a per-test namespace below the run ID for tests that create durable or committed data.
- Clean database rows, Redis keys, locks, and stream entries by namespace during teardown.
- Make cleanup idempotent so reruns can safely clean stale data.
- Do not let a new test reuse keys from a failed test; create a fresh namespace or fail before mixing data.

## Required Early Behaviors

- Guest shopper can browse tenant-aware products.
- Guest shopper can add a variant to cart.
- Guest shopper can create or resume checkout state.
- Checkout state recalculates after address, shipping, payment, and basket changes.
- Order confirmation is idempotent.
- Cross-tenant data access is rejected or impossible by repository design.
- Race conditions are covered for idempotency, inventory reservation, checkout state writes, and outbox publishing.

## Tooling Direction

- Pest feature/API tests are the default executable test layer.
- Gherkin feature files may live beside tests as readable specifications.
- Contract tests should validate OpenAPI responses and RFC 9457 Problem Details.
- End-to-end browser tests can be added after the Blade happy path exists.
