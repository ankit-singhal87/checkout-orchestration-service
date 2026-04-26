# Seed Fixtures

These files are source data for deterministic local demos and tests. Laravel seeders should read these fixtures and write idempotent records to MySQL, Redis, and OpenSearch projections.

Rules:

- Shared demo fixtures use stable IDs.
- Test data must not mutate shared demo fixtures.
- Test-only records use unique run/test namespaces and self-cleanup.
- Faker-generated values must use explicit scenario seeds.
