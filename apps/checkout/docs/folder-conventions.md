# Checkout App Folder Conventions

The checkout app starts as a Laravel modular monolith with Clean Architecture / hexagonal boundaries. See `../../../docs/adr/0006-laravel-clean-boundaries.md`.

Dependency direction:

```text
Http / Console / Infrastructure -> Application -> Domain
```

The folder names below describe ownership before full implementation code exists.

## Application Layers

- `app/Domain`: checkout, cart, order, tenant, catalog, pricing, inventory, and identity domain concepts.
- `app/Application`: use cases, command/query handlers, orchestration services, and transaction boundaries.
- `app/Infrastructure`: Eloquent repositories, Redis adapters, OpenSearch projections, outbox storage, and external integration adapters.
- `app/Http`: controllers, requests, middleware, exception rendering, and API resource presenters.
- `resources/views`: Blade templates that receive explicit view models.
- `routes`: Blade and API route declarations.
- `database`: migrations, seeders, and factories.
- `tests`: feature, integration, contract, and unit tests.

## Rules

- Controllers call application services. They do not contain domain decisions.
- Blade views render view models. They do not query databases.
- Domain objects do not depend on HTTP, Redis, OpenSearch, or framework-specific request classes.
- Repositories and adapters enforce tenant context.
- Order confirmation is an application-service transaction boundary.
- Async side effects leave through the outbox after committed writes.

## Avoid

- Fat controllers that contain checkout orchestration.
- Route closures with business behavior.
- Eloquent models that own state-machine or idempotency rules.
- Blade templates that query repositories, models, Redis, or service containers.
- Tenant-scoped repository methods without explicit tenant context.

## Initial Bounded Contexts

- Tenant
- Catalog
- Cart
- Checkout
- Order
- Inventory
- Pricing
- Identity