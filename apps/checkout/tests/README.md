# Tests

Checkout app tests should grow with implementation risk.

Use BDD scenarios to describe expected behavior and TDD to implement the smallest tested increment.

Use Pest for executable Laravel tests. Broad test runs should use Pest parallel execution once the app is generated.

Persistence tests use real MySQL through Docker or CI services. Do not use SQLite for checkout-critical integration coverage.

Every executable test must be idempotent and self-cleaning. Use unique run/test namespaces for database rows, Redis keys, locks, idempotency keys, outbox rows, and Faker seeds. Failed-test leftovers must never be reused by another test.

Required before the happy path is considered complete:

- Tenant isolation tests.
- Checkout state transition tests.
- Order confirmation idempotency tests.
- Problem Details response tests.
- Guest checkout feature test.
- Concurrent execution tests for race conditions around idempotency, stock reservation, and state updates.