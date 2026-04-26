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

exec php-fpm -F
