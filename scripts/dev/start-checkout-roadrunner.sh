#!/usr/bin/env sh
set -eu

if [ ! -f artisan ]; then
  echo "Laravel app is not bootstrapped; keeping checkout container alive."
  tail -f /dev/null
fi

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi
if ! php -r '$env = file_exists(".env") ? file_get_contents(".env") : ""; exit(preg_match("/^APP_KEY=base64:.+/m", $env) ? 0 : 1);'; then
  php artisan key:generate --force
fi
mkdir -p bootstrap/cache storage/app/public storage/framework/cache storage/framework/sessions storage/framework/testing storage/framework/views storage/logs
chmod -R a+rwX bootstrap/cache storage
php artisan migrate --force
php artisan db:seed --force
php artisan optimize:clear

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

APP_BASE_PATH="$(pwd)" \
LARAVEL_OCTANE=1 \
exec ./rr \
  -c .rr.yaml \
  -o "http.pool.num_workers=${OCTANE_WORKERS:-2}" \
  -o "http.pool.max_jobs=${OCTANE_MAX_REQUESTS:-500}" \
  serve
