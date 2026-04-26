# Checkout App Testing Strategy

Tests should prove tenant isolation, checkout consistency, idempotency, and the guest happy path before service extraction. Use BDD to describe behavior and TDD to drive implementation.

## Test Runner And Libraries

- Use Pest as the primary Laravel test runner.
- Run tests in parallel by default in CI and local verification once the Laravel app is generated.
- Use Faker for deterministic fixture variation, seeded through explicit scenario factories.
- Use Mockery for external seams such as payment simulation, carrier/collection point simulation, clocks, and event publishers.
- Prefer Laravel fakes only where they keep the test closer to framework behavior than a hand-rolled mock.

## BDD/TDD Workflow

- Start with a behavior scenario for user-facing or business-critical changes.
- Add a failing unit, feature, API, integration, or contract test before implementation.
- Implement the smallest useful code to pass the test.
- Refactor after tests are green.
- Add integration coverage when behavior crosses persistence, cache, outbox, or tenant boundaries.

## Test Layers

- Unit tests for domain rules and value objects.
- Feature tests for Blade route flows.
- API tests for checkout state endpoints.
- Integration tests for MySQL, Redis, outbox, and tenant-scoped repositories.
- Contract tests for Problem Details and OpenAPI examples.
- Behavior specs for checkout flows and tenant isolation scenarios.
- Concurrency tests for idempotency, inventory reservation, checkout state updates, and outbox publishing.

## Database Policy

Use a real database for persistence tests. SQLite is not a substitute for MySQL behavior in checkout-critical coverage because locking, transactions, constraints, JSON behavior, and concurrency semantics differ.

Database-backed tests should:

- Run against the local MySQL container or an equivalent CI MySQL service.
- Use migrations and isolated test schemas/databases.
- Reset data between tests with Laravel database testing tools or explicit truncation/transactions where safe.
- Keep tenant fixtures deterministic even when Faker generates values.
- Avoid mocking repositories in integration tests that are meant to prove tenant scoping and transaction behavior.

## Test Isolation And Cleanup

Tests must be idempotent and self-cleaning. A failed test must not leave data, Redis keys, idempotency keys, locks, or outbox rows that a later test can accidentally reuse.

Isolation rules:

- Generate a unique test run ID for each process and include it in tenant slugs, cart IDs, checkout state IDs, idempotency keys, Redis keys, lock names, outbox test rows, and Faker seeds.
- Use per-test namespaces inside the run ID when tests create shared-looking resources.
- Never use production-like fixed keys such as `fashion-store:test-cart` in executable tests unless the key also includes the run/test namespace.
- Prefer database transactions for tests that do not need to observe commits across processes.
- For committed or concurrent tests, tag rows with the run/test namespace and delete them in teardown.
- Clean Redis keys by namespace in teardown, including locks, idempotency entries, streams, and cache entries.
- Make teardown safe to run more than once.
- Before a test starts, assert that its namespace is empty or create a fresh namespace rather than reusing leftover keys.
- If cleanup fails, fail loudly and keep later tests from using that namespace.

## Parallel And Concurrent Testing

Pest parallel execution is the default for broad test runs. Tests that intentionally exercise race conditions should run against isolated records and may be grouped separately when they need stricter process coordination.

Required concurrent execution coverage:

- Two confirmations with the same idempotency key create one order.
- Two confirmations with different keys cannot oversell the same tenant/variant.
- Concurrent checkout state updates do not lose the latest valid transition.
- Outbox publishing remains idempotent when a worker retries or two workers observe the same pending row.
- Tenant-scoped locks and cache keys cannot affect another tenant's checkout state.

## Phase 1 Test Targets

- Route names and expected request methods are documented.
- BDD scenarios exist for the initial guest checkout happy path and failure paths.
- View model shapes are covered by focused tests once classes exist.
- Tenant context is required for all repository access once persistence exists.
- Idempotency tests are required before order confirmation is considered complete.

## CI Rule

Use shared scripts under [scripts/ci](../../../scripts/ci) so GitLab CI stays the source of truth. Until the Laravel app exists, CI validates behavior specs and skips executable app tests. After the app exists, CI should run Pest in parallel against a real MySQL service or the checkout Docker Compose profile.
