#!/usr/bin/env sh
set -eu

app_dir="apps/checkout"
project_name="${ORDER_PROCESSOR_RUNTIME_PROJECT:-checkout-order-processor-smoke}"
override_file="${TMPDIR:-/tmp}/checkout-order-processor-smoke.$$.yml"

cleanup() {
  COMPOSE_PROFILES=worker \
  COMPOSE_PROJECT_NAME="$project_name" \
    docker compose -f docker-compose.yml -f "$override_file" down -v --remove-orphans >/dev/null 2>&1 || true
  rm -f "$override_file"
}
trap cleanup EXIT INT TERM

if [ ! -f "$app_dir/artisan" ]; then
  echo "Laravel app is not generated yet; skipping order processor runtime smoke test."
  exit 0
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required for the order processor runtime smoke test." >&2
  exit 1
fi

cat >"$override_file" <<'YAML'
services:
  mysql:
    ports: !reset []
  redis:
    ports: !reset []
YAML

if [ ! -f "$app_dir/vendor/autoload.php" ]; then
  COMPOSE_PROFILES=worker \
  COMPOSE_PROJECT_NAME="$project_name" \
    docker compose -f docker-compose.yml -f "$override_file" build checkout-order-processor

  COMPOSE_PROFILES=worker \
  COMPOSE_PROJECT_NAME="$project_name" \
    docker compose -f docker-compose.yml -f "$override_file" run --rm --no-deps --entrypoint composer checkout-order-processor install --no-interaction --prefer-dist
fi

COMPOSE_PROFILES=worker \
COMPOSE_PROJECT_NAME="$project_name" \
  docker compose -f docker-compose.yml -f "$override_file" run --rm --no-deps --entrypoint php checkout-order-processor artisan list --raw \
    | grep -Eq '^checkout:order-processor:consume([[:space:]]|$)'

echo "Order processor runtime smoke test found checkout:order-processor:consume."
