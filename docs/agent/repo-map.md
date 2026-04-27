# Repository Map

- `apps/checkout` - Laravel checkout application, Blade UI, REST routes, domain/application/infrastructure code, and tests.
- `apps/checkout/app/Application/Checkout/CheckoutManager.php` - checkout orchestration flow and transaction boundary.
- `infra/local/caddy/Caddyfile` - local Caddy edge parity configuration.
- `scripts/test/checkout-parity.sh` - HTTPS/H1/H2/H3 edge smoke validation.
- `docker/` - container build and runtime configuration.
- `.gitlab-ci.yml` - primary CI pipeline definition.
- `Makefile` - canonical local command entrypoint.
- `docs/human` - reviewer-facing architecture, ADRs, risks, and planning.
- `docs/agent` - agent operating context, contracts, standards, and workflow.
