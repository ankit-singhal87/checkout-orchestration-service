# Checkout App

Laravel is the first application boundary for the MVP. It owns the Blade UI, public checkout API, application services, validation, persistence, idempotency, and checkout orchestration.

Application code targets PHP 8.5 and follows [docs/agent/coding-standards/php-8.5.md](../../docs/agent/coding-standards/php-8.5.md).

Blade is included with Laravel. Blade templates live under `resources/views`, and route/controller code returns them with Laravel's `view()` helper.

## Phase 1 Scope

- Keep the Laravel project aligned with the checkout folder conventions.
- Keep checkout usable without login.
- Keep controller, application service, repository, view model, and domain folder conventions explicit as behavior grows.
- Keep Blade templates free of database queries and domain decisions.

## Not In Phase 1

- Real payment provider integration.
- Full auth requirement for shopping or checkout.
- Extracting pricing, inventory, payment, or order into separate services.

## Phase 2 Runtime

The default local runtime is Nginx plus PHP-FPM over HTTP. `make up-app` starts this stack with MySQL and Redis for normal local development and browser checks.

`make up-roadrunner` starts the optional RoadRunner/Octane performance profile. `make up-parity` adds the local-production Caddy edge path with HTTPS, HTTP/1.1, HTTP/2, HTTP/3 over QUIC/UDP 443, forwarded headers, security headers, and request-size limits.

## Bootstrap

The host does not need PHP or Composer. Bootstrap is handled through Docker and exits when `artisan` already exists:

```bash
sh scripts/dev/bootstrap-checkout-app.sh
```

After bootstrapping, review generated files before committing. The app should continue to follow the folder conventions in [apps/checkout/docs](docs).

## Dependency Versioning

Composer and npm dependencies should use semver-compatible constraints, such as `^13.0` for Laravel packages or `^8.0.0` for Vite, so compatible minor and patch updates can be adopted automatically through normal update workflows.
