#!/usr/bin/env sh
set -eu

if [ ! -f artisan ]; then
  echo "Laravel app is not bootstrapped; keeping checkout container alive."
  tail -f /dev/null
fi

if ! php artisan list --raw | grep -qx "octane:start"; then
  echo "RoadRunner runtime requires Laravel Octane." >&2
  echo "After dependency approval, install laravel/octane, spiral/roadrunner-cli, and spiral/roadrunner-http." >&2
  exit 1
fi

if [ ! -x vendor/bin/rr ]; then
  echo "RoadRunner binary wrapper is missing at vendor/bin/rr." >&2
  echo "After dependency approval, run vendor/bin/rr get-binary inside the checkout container." >&2
  exit 1
fi

exec php artisan octane:start \
  --server=roadrunner \
  --host=0.0.0.0 \
  --port=8000 \
  --workers="${OCTANE_WORKERS:-2}" \
  --max-requests="${OCTANE_MAX_REQUESTS:-500}"
