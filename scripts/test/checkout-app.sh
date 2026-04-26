#!/usr/bin/env sh
set -eu

app_dir="apps/checkout"

if [ ! -d "$app_dir" ]; then
  echo "Missing checkout app directory: $app_dir" >&2
  exit 1
fi

if [ -f "$app_dir/artisan" ]; then
  if [ "${CHECKOUT_TEST_RUNTIME:-}" = "host" ]; then
    export DB_HOST="${DB_HOST:-127.0.0.1}"
    export DB_PORT="${DB_PORT:-3306}"
    export DB_DATABASE="${DB_DATABASE:-checkout_testing}"
    export DB_USERNAME="${DB_USERNAME:-root}"
    export DB_PASSWORD="${DB_PASSWORD:-checkout_root}"
    (cd "$app_dir" && php artisan test --parallel --recreate-databases)
  elif docker compose version >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
    export COMPOSE_PROFILES="${COMPOSE_PROFILES:-app}"
    export DB_DATABASE="${DB_DATABASE:-checkout_testing}"
    export DB_USERNAME="${DB_USERNAME:-root}"
    export DB_PASSWORD="${DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-checkout_root}}"
    docker compose up -d mysql
    docker compose exec -T mysql mysql -uroot -p"${MYSQL_ROOT_PASSWORD:-checkout_root}" \
      -e "CREATE DATABASE IF NOT EXISTS checkout_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    docker compose run --rm \
      -e DB_DATABASE="$DB_DATABASE" \
      -e DB_USERNAME="$DB_USERNAME" \
      -e DB_PASSWORD="$DB_PASSWORD" \
      checkout php artisan test --parallel --recreate-databases
  elif command -v php >/dev/null 2>&1; then
    (cd "$app_dir" && php artisan test --parallel --recreate-databases)
  else
    echo "PHP or a reachable Docker daemon with Docker Compose is required to run checkout app tests." >&2
    exit 1
  fi
else
  echo "Laravel app is not generated yet; skipping executable checkout app tests."
  echo "Behavior specs are validated by scripts/test/behavior-specs.sh."
fi
