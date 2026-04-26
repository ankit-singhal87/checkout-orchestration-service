#!/usr/bin/env sh
set -eu

required_paths="
apps/checkout/README.md
apps/checkout/artisan
apps/checkout/composer.json
apps/checkout/composer.lock
apps/checkout/phpunit.xml
apps/checkout/docs/folder-conventions.md
apps/checkout/docs/routes.md
apps/checkout/docs/testing-strategy.md
apps/checkout/docs/view-models.md
apps/checkout/app/Domain/README.md
apps/checkout/app/Application/README.md
apps/checkout/app/Infrastructure/README.md
apps/checkout/app/Http/README.md
apps/checkout/resources/views/README.md
apps/checkout/routes/README.md
apps/checkout/routes/api.php.stub
apps/checkout/routes/web.php.stub
apps/checkout/database/README.md
apps/checkout/tests/README.md
apps/checkout/tests/Pest.php
apps/checkout/tests/TestCase.php
apps/checkout/tests/Behavior/README.md
apps/checkout/tests/Behavior/features/guest-checkout.feature
apps/checkout/tests/Behavior/features/tenant-browsing.feature
apps/checkout/tests/Behavior/features/checkout-state.feature
apps/checkout/tests/Behavior/features/checkout-config.feature
apps/checkout/tests/Behavior/features/checkout-failures.feature
apps/checkout/tests/Behavior/features/checkout-resume.feature
apps/checkout/tests/Behavior/features/cart-management.feature
apps/checkout/tests/Behavior/features/concurrent-checkout.feature
apps/checkout/tests/Behavior/features/idempotent-order-confirmation.feature
apps/checkout/tests/Behavior/features/shipping-and-payment.feature
apps/checkout/tests/Behavior/features/tenant-isolation.feature
apps/checkout/tests/Feature/README.md
apps/checkout/tests/Unit/README.md
services/inventory-service/README.md
workers/order-processor/README.md
workers/outbox-publisher/README.md
proto/README.md
seed/README.md
seed/fixtures/README.md
seed/fixtures/catalog-sample.json
seed/fixtures/tenants.json
infra/terraform/README.md
infra/k8s/README.md
docker/README.md
docker/checkout.Dockerfile
docs/human/phase-1-foundation.md
docs/agent/api/openapi.checkout.yaml
docs/agent/contracts/tenant-model.md
docs/agent/contracts/checkout-state.md
docs/agent/contracts/problem-details.md
docs/agent/contracts/domain-events.md
docs/agent/contracts/seed-data.md
docs/agent/contracts/latency-slos.md
docs/agent/contracts/bdd-tdd.md
docs/agent/coding-standards/php-8.5.md
docs/human/adr/0006-laravel-clean-boundaries.md
scripts/test/behavior-specs.sh
scripts/test/checkout-app.sh
scripts/test/route-stubs.sh
scripts/test/seed-fixtures.sh
scripts/git/check-migration-immutability.sh
scripts/git/pre-push.sh
scripts/git/install-hooks.sh
scripts/dev/bootstrap-checkout-app.sh
scripts/dev/start-checkout.sh
"

missing=0

for path in $required_paths; do
  if [ ! -f "$path" ]; then
    echo "Missing required Phase 1 foundation file: $path" >&2
    missing=1
  fi
done

if [ "$missing" -ne 0 ]; then
  exit 1
fi

sh scripts/test/behavior-specs.sh
sh scripts/test/route-stubs.sh
sh scripts/test/seed-fixtures.sh

echo "Phase 1 foundation validation passed."
