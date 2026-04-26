# PHP 8.5 Coding Standards

The checkout app targets PHP 8.5 and Laravel 13. Prefer modern PHP syntax while keeping the code easy to test, review, and refactor.

## Baseline

- Use `declare(strict_types=1);` in new non-Laravel-generated PHP files.
- Follow PSR-12 formatting through Laravel Pint.
- Use semver-compatible Composer constraints for libraries that follow semantic versioning, usually caret constraints such as `^13.0`, so minor and patch updates stay available.
- Use typed properties, typed parameters, typed return values, and precise collection/value-object PHPDoc where generics are useful.
- Prefer constructor property promotion and `readonly` properties for immutable dependencies and value objects.
- Prefer enums for closed sets such as checkout state status, payment simulation result, and tenant feature flags.
- Prefer `match` for exhaustive value mapping.
- Keep nullability explicit. Do not use `mixed` unless the boundary really accepts unknown data.

## Laravel Application Style

- Controllers stay thin: validate input, call an application service, and return a response/view model.
- Application services own transaction boundaries and orchestration.
- Domain objects do not depend on HTTP requests, Eloquent models, Redis clients, OpenSearch clients, or Blade.
- Repositories/adapters must require tenant context for tenant-scoped reads and writes.
- Blade views receive explicit view models and must not query the database.
- Public API errors use RFC 9457 Problem Details.

## Testing Style

- Use Pest for executable tests.
- Run broad test suites with Pest parallel execution.
- Use real MySQL for persistence, transaction, locking, and tenant-isolation tests.
- Use Faker with explicit seeds and test namespaces.
- Use Mockery for external collaborators and clocks, not for replacing persistence in integration tests.
- Test data must be idempotent and self-cleaning.

## Error Handling

- Throw domain-specific exceptions for expected business failures.
- Convert public HTTP failures to Problem Details at the boundary.
- Do not leak SQL, stack traces, tokens, secrets, or internal tenant IDs.
- Include request/trace IDs in public error responses when available.

## Naming

- Use domain language from the contracts: tenant, shop, cart, checkout state, order, idempotency key, outbox event.
- Suffix immutable data carriers with `Data`, read-facing render objects with `ViewModel`, and external adapters with `Adapter` only when the role is otherwise unclear.
- Avoid generic names such as `Manager`, `Helper`, or `Util` for domain code.

## Static Analysis Direction

Add PHPStan or Larastan after the first application slice exists. Until then, keep code type-complete enough that static analysis can be introduced without broad rewrites.