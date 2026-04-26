#!/usr/bin/env sh
set -eu

if [ ! -f artisan ]; then
  echo "Laravel app is not bootstrapped; keeping checkout container alive."
  tail -f /dev/null
fi

if ! php artisan list --raw | grep -Eq "^octane:start([[:space:]]|$)"; then
  echo "RoadRunner runtime requires Laravel Octane." >&2
  echo "Run composer install inside apps/checkout and verify laravel/octane, spiral/roadrunner-cli, and spiral/roadrunner-http are installed." >&2
  exit 1
fi

if [ ! -x vendor/bin/rr ]; then
  echo "RoadRunner binary wrapper is missing at vendor/bin/rr." >&2
  echo "Run composer install inside apps/checkout before starting the RoadRunner runtime." >&2
  exit 1
fi

if [ ! -x rr ]; then
  echo "RoadRunner server binary is missing at ./rr; downloading it with vendor/bin/rr."

  if ! vendor/bin/rr get-binary -n; then
    echo "RoadRunner server binary could not be downloaded." >&2
    echo "Run vendor/bin/rr get-binary inside apps/checkout or inside the checkout container and retry." >&2
    exit 1
  fi

  chmod +x rr
fi

exec php artisan octane:start \
  --server=roadrunner \
  --host=0.0.0.0 \
  --port=8000 \
  --workers="${OCTANE_WORKERS:-2}" \
  --max-requests="${OCTANE_MAX_REQUESTS:-500}"
