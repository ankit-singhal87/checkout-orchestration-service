# Checkout App

Laravel is the first application boundary for the MVP. It owns the Blade UI, public checkout API, application services, validation, persistence, idempotency, and checkout orchestration.

Application code targets PHP 8.5 and follows `../../docs/coding-standards/php-8.5.md`.

Blade is included with Laravel. Blade templates live under `resources/views`, and route/controller code returns them with Laravel's `view()` helper.

## Phase 1 Scope

- Keep the generated Laravel project skeleton aligned with the checkout folder conventions.
- Keep checkout usable without login.
- Define controller, application service, repository, view model, and domain folder conventions before adding behavior.
- Keep Blade templates free of database queries and domain decisions.
- Use RoadRunner for the production-style runtime once the Laravel app exists.

## Not In Phase 1

- Real payment provider integration.
- Full auth requirement for shopping or checkout.
- Extracting pricing, inventory, payment, or order into separate services.

## Bootstrap

The host does not need PHP or Composer. Bootstrap is handled through Docker and exits when `artisan` already exists:

```bash
sh scripts/dev/bootstrap-checkout-app.sh
```

After bootstrapping, review generated files before committing. The app should continue to follow the folder conventions in `apps/checkout/docs`.

## Dependency Versioning

Composer and npm dependencies should use semver-compatible constraints, such as `^13.0` for Laravel packages or `^8.0.0` for Vite, so compatible minor and patch updates can be adopted automatically through normal update workflows.
