#!/usr/bin/env sh
set -eu

if [ ! -f artisan ]; then
  echo "Laravel app is not bootstrapped; keeping checkout container alive."
  tail -f /dev/null
fi

php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --force

exec php artisan serve --host=0.0.0.0 --port=8000
