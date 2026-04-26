# PHP 8.5 Coding Standards

The checkout app targets PHP 8.5 and Laravel 13. Use the newest language features when they make code smaller, more explicit, or harder to misuse. Do not use novelty syntax when a plain Laravel or PHP construct is clearer.

## Baseline

- Use `declare(strict_types=1);` in new non-Laravel-generated PHP files.
- Follow PSR-12 formatting through Laravel Pint.
- Use semver-compatible Composer constraints for libraries that follow semantic versioning, usually caret constraints such as `^13.0`, so minor and patch updates stay available.
- Type every property, parameter, and return value. Use precise PHPDoc only where PHP cannot express the shape, such as `list<T>`, array shapes, and Laravel collection generics.
- Keep nullability explicit. Do not use `mixed` unless the boundary genuinely accepts unknown data.
- Prefer `final` classes for application services, value objects, view models, controllers, middleware, and adapters unless extension is an intended design point.
- Prefer constructor property promotion and `readonly` for immutable dependencies and data carriers.
- Use `final` promoted properties where inheritance exists and the property contract must not be redefined.
- Prefer enums for closed sets such as checkout state, cart add result, stock state, payment simulation result, tenant feature flags, and outbox event type.
- Prefer `match` for exhaustive value mapping. Avoid long `if`/`elseif` chains when matching a closed enum or known scalar set.
- Use named arguments when they make call sites self-documenting, especially for value objects and view models with multiple same-type arguments.
- Add concise PHPDoc to every class, enum, and method. Explain the role or invariant; do not repeat the method signature in prose.

## PHP 8.5 Features

- Use the PHP 8.5 URI extension for parsing, normalizing, and modifying URLs. Do not use `parse_url()` for security-sensitive URL handling, callback URLs, webhook origins, tenant host validation, or redirect targets.
- Use the pipe operator `|>` for short, pure, left-to-right transformations where it removes temporary variables or nested function calls. Do not use it for Eloquent builders, Laravel collections that already chain well, transactions, logging, IO, or any flow with hidden side effects.
- Use `clone($object, [...])` for "with" methods on immutable `readonly` objects instead of rebuilding every constructor argument by hand.
- Mark methods with `#[\NoDiscard]` when ignoring the returned value would be a bug, such as validation results, idempotency decisions, lock acquisition, checkout state transitions, or payment authorization outcomes.
- Use `array_first()` and `array_last()` instead of `reset()`, `end()`, or manual indexing when the intent is to read the first or last array value.
- Use `Locale::isRightToLeft()` or `locale_is_right_to_left()` for locale-driven UI direction instead of maintaining ad hoc RTL locale lists.
- Use `get_error_handler()` and `get_exception_handler()` only in framework/tooling diagnostics. Application code should not branch business behavior on global handlers.
- Use persistent cURL share handles only in dedicated HTTP adapters after measuring connection setup cost. Prefer Laravel HTTP client defaults until latency data justifies lower-level cURL management.
- Use `php --ini=diff`, `PHP_BUILD_PROVIDER`, and `PHP_BUILD_DATE` in support/debug docs and CI diagnostics when environment drift is suspected.
- Avoid PHP 8.5-deprecated aliases and casts, including non-canonical scalar casts such as `(boolean)`, `(integer)`, `(double)`, and `(binary)`. Use `(bool)`, `(int)`, `(float)`, and `(string)`.

## Simplicity And Latency

- Optimize for clear request paths: validate input, resolve tenant once, call an application service, return a response or view model.
- Avoid speculative abstractions. Add interfaces only at real boundaries: external payment providers, message brokers, object storage, search, identity, or time/UUID generation.
- Avoid allocation-heavy collection pipelines in hot paths when a small `foreach` is clearer and faster. Laravel collections are fine for readability outside tight loops and large result sets.
- Do not hydrate more Eloquent models than needed. Use scoped queries, eager loading, projections, `exists()`, and aggregate queries to keep checkout requests low latency.
- Do not query from Blade. Views receive explicit view models or scalar DTOs only.
- Keep transactions short. Perform database reads/writes that must be atomic inside the transaction; perform rendering, HTTP calls, logging decoration, and event dispatch outside when possible.
- Prefer idempotent writes with database constraints over read-then-write checks that can race.
- Use queues/outbox events for slow external side effects. Keep customer-facing checkout responses bounded and predictable.

## Laravel Application Style

- Controllers stay thin: validate input, call an application service, and return a response/view model.
- Application services own transaction boundaries and orchestration.
- Domain objects do not depend on HTTP requests, Eloquent models, Redis clients, OpenSearch clients, or Blade.
- Repositories/adapters must require tenant context for tenant-scoped reads and writes.
- Public API errors use RFC 9457 Problem Details.
- Use framework helpers when they clarify intent, but do not hide domain decisions in global helpers, macros, or facades.
- Keep dependency injection explicit. Constructor injection is preferred for services; method injection is acceptable for request-scoped Laravel objects.

## Error Handling

- Throw domain-specific exceptions for expected business failures when a returned result object would make call sites noisier. Use result objects for common branch outcomes that callers are expected to handle.
- Convert public HTTP failures to Problem Details at the boundary.
- Do not leak SQL, stack traces, tokens, secrets, payment details, or internal tenant IDs.
- Include request/trace IDs in public error responses when available.
- Prefer fail-closed behavior for tenant resolution, authorization, idempotency, and payment state transitions.

## Testing Style

- Use Pest for executable tests.
- Run broad suites with Laravel/Pest parallel execution.
- Use real MySQL for persistence, transaction, locking, and tenant-isolation tests.
- Use Faker with explicit seeds and test namespaces.
- Use Mockery for external collaborators and clocks, not for replacing persistence in integration tests.
- Test data must be idempotent and self-cleaning.
- Seed deterministic fixtures in each isolated test database when tests depend on shared demo catalog data.
- Cover race-prone flows with concurrency or lock-oriented tests before adding distributed infrastructure.

## Naming

- Use domain language from the contracts: tenant, shop, cart, checkout state, order, idempotency key, outbox event.
- Suffix immutable input/output data carriers with `Data` when the role is not obvious, read-facing render objects with `ViewModel`, and external adapters with `Adapter`.
- Avoid generic names such as `Manager`, `Helper`, `Util`, `Processor`, or `Handler` for domain code.
- Prefer verbs for application services that perform commands, such as `AddCartItem`, `AuthorizePayment`, or `ReserveInventory`.

## Static Analysis Direction

Add PHPStan or Larastan after the first application slice exists. Until then, keep code type-complete enough that static analysis can be introduced without broad rewrites.