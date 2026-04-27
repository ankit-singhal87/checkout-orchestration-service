#!/usr/bin/env sh
set -eu

if [ ! -f artisan ]; then
  echo "Laravel app is not bootstrapped; keeping order processor container alive."
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

interval="${ORDER_PROCESSOR_INTERVAL_SECONDS:-5}"
limit="${ORDER_PROCESSOR_LIMIT:-10}"
block_ms="${ORDER_PROCESSOR_BLOCK_MS:-1000}"
consumer="${ORDER_PROCESSOR_CONSUMER:-order-processor-$(hostname)}"

case "$interval" in
  ''|*[!0-9]*)
    echo "ORDER_PROCESSOR_INTERVAL_SECONDS must be a non-negative integer." >&2
    exit 1
    ;;
esac

case "$limit" in
  ''|*[!0-9]*)
    echo "ORDER_PROCESSOR_LIMIT must be a positive integer." >&2
    exit 1
    ;;
esac

case "$block_ms" in
  ''|*[!0-9]*)
    echo "ORDER_PROCESSOR_BLOCK_MS must be a non-negative integer." >&2
    exit 1
    ;;
esac

if [ "$limit" -lt 1 ]; then
  echo "ORDER_PROCESSOR_LIMIT must be a positive integer." >&2
  exit 1
fi

stop_requested=0
trap 'stop_requested=1' INT TERM

echo "Starting checkout order processor with consumer ${consumer}, limit ${limit}, block ${block_ms}ms, and interval ${interval}s."

while [ "$stop_requested" -eq 0 ]; do
  if ! php artisan checkout:order-processor:consume --limit="$limit" --consumer="$consumer" --block-ms="$block_ms"; then
    echo "Order processor command failed; exiting for Compose restart policy." >&2
    exit 1
  fi

  if [ "$interval" -eq 0 ]; then
    break
  fi

  sleep "$interval" &
  wait "$!" || true
done

echo "Checkout order processor stopped."
