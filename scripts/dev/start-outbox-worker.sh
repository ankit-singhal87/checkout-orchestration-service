#!/usr/bin/env sh
set -eu

if [ ! -f artisan ]; then
  echo "Laravel app is not bootstrapped; keeping outbox worker container alive."
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

interval="${OUTBOX_WORKER_INTERVAL_SECONDS:-5}"
limit="${OUTBOX_WORKER_LIMIT:-10}"

case "$interval" in
  ''|*[!0-9]*)
    echo "OUTBOX_WORKER_INTERVAL_SECONDS must be a non-negative integer." >&2
    exit 1
    ;;
esac

case "$limit" in
  ''|*[!0-9]*)
    echo "OUTBOX_WORKER_LIMIT must be a positive integer." >&2
    exit 1
    ;;
esac

if [ "$limit" -lt 1 ]; then
  echo "OUTBOX_WORKER_LIMIT must be a positive integer." >&2
  exit 1
fi

stop_requested=0
trap 'stop_requested=1' INT TERM

echo "Starting checkout outbox worker loop with limit ${limit} and interval ${interval}s."

while [ "$stop_requested" -eq 0 ]; do
  if ! php artisan checkout:outbox:publish --limit="$limit"; then
    echo "Outbox publisher command failed; exiting for Compose restart policy." >&2
    exit 1
  fi

  if [ "$interval" -eq 0 ]; then
    break
  fi

  sleep "$interval" &
  wait "$!" || true
done

echo "Checkout outbox worker stopped."
