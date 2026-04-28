# ADR 0006: Laravel Modular Monolith With Clean Boundaries

## Status

Accepted

## Context

The checkout MVP starts in Laravel to keep the first working UI/API path fast, local-first, and easy to demo. Laravel provides useful framework conventions, Eloquent, routing, validation, queues, Blade, and testing tools. At the same time, checkout has business-critical rules around tenant isolation, state transitions, idempotency, order creation, and async side effects.

If those rules are placed directly in controllers, Blade templates, route closures, or Eloquent models, the app will become hard to test and hard to split later.

## Decision

Use Laravel as a modular monolith with Clean Architecture / hexagonal boundaries.

Dependency direction:

```text
Http / Console / Infrastructure -> Application -> Domain
```

Layer responsibilities:

- `app/Domain`: domain concepts, value objects, domain services, domain errors, and state-machine rules. No HTTP, Blade, Redis, OpenSearch, Eloquent model, or framework request dependencies.
- `app/Application`: use cases, command/query handlers, transaction boundaries, idempotency orchestration, and outbox coordination.
- `app/Infrastructure`: Eloquent persistence, Redis adapters, OpenSearch projections, event/outbox storage, and external simulation adapters.
- `app/Http`: controllers, form requests, middleware, exception rendering, API presenters, and web responses.
- `resources/views`: Blade templates that render explicit view models only.

Laravel framework code remains welcome at the edges. The goal is not framework avoidance; the goal is keeping checkout business rules portable, testable, and protected from UI/persistence concerns.

## Consequences

- Controllers should be thin and delegate to application services.
- Eloquent models should not become the home for checkout orchestration rules.
- Blade templates must not query the database or make domain decisions.
- Tenant context must enter repositories/adapters explicitly.
- Order confirmation is an application-service transaction boundary.
- Go extraction remains optional later because stable application/domain boundaries already exist inside Laravel.

## Anti-Patterns

- Fat controllers that calculate totals, choose shipping, or confirm orders.
- Route closures that contain checkout behavior.
- Blade templates that call repositories, Eloquent models, Redis, or service containers for business decisions.
- Domain objects depending on Laravel request objects, Eloquent models, Redis clients, OpenSearch clients, or HTTP responses.
- Repository methods that can run without tenant context for tenant-scoped data.
