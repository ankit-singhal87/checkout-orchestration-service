#!/usr/bin/env sh
set -eu

if [ ! -f artisan ]; then
  echo "Laravel app is not bootstrapped; keeping checkout container alive."
  tail -f /dev/null
fi

php artisan optimize:clear
if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi
if ! php -r '$env = file_exists(".env") ? file_get_contents(".env") : ""; exit(preg_match("/^APP_KEY=base64:.+/m", $env) ? 0 : 1);'; then
  php artisan key:generate --force
fi
php artisan migrate --force
php artisan db:seed --force

if [ "${CHECKOUT_RUNTIME:-artisan}" = "roadrunner" ]; then
  exec sh /scripts/dev/start-checkout-roadrunner.sh
fi

exec php artisan serve --host=0.0.0.0 --port=8000
