#!/usr/bin/env sh
set -eu

app_dir="apps/checkout"
port="${CHECKOUT_RUNTIME_PORT:-18080}"
project_name="${CHECKOUT_RUNTIME_PROJECT:-checkout-runtime-smoke}"
curl_image="${CHECKOUT_RUNTIME_CURL_IMAGE:-curlimages/curl:8.5.0}"
override_file="${TMPDIR:-/tmp}/checkout-runtime-smoke.$$.yml"

cleanup() {
  COMPOSE_PROFILES=performance \
  COMPOSE_PROJECT_NAME="$project_name" \
  ROADRUNNER_PORT="$port" \
    docker compose -f docker-compose.yml -f "$override_file" down -v --remove-orphans >/dev/null 2>&1 || true
  rm -f "$override_file"
}
trap cleanup EXIT INT TERM

if [ ! -f "$app_dir/artisan" ]; then
  echo "Laravel app is not generated yet; skipping RoadRunner runtime smoke test."
  exit 0
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required for the RoadRunner runtime smoke test." >&2
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
  COMPOSE_PROFILES=performance \
  COMPOSE_PROJECT_NAME="$project_name" \
  ROADRUNNER_PORT="$port" \
    docker compose -f docker-compose.yml -f "$override_file" build checkout-roadrunner

  COMPOSE_PROFILES=performance \
  COMPOSE_PROJECT_NAME="$project_name" \
  ROADRUNNER_PORT="$port" \
    docker compose -f docker-compose.yml -f "$override_file" run --rm --no-deps --entrypoint composer checkout-roadrunner install --no-interaction --prefer-dist
fi

COMPOSE_PROFILES=performance \
COMPOSE_PROJECT_NAME="$project_name" \
ROADRUNNER_PORT="$port" \
  docker compose -f docker-compose.yml -f "$override_file" up -d --build checkout-roadrunner

retries=30
until docker run --rm --network "${project_name}_default" "$curl_image" -fsS "http://checkout-roadrunner:8000/up" >/dev/null 2>&1; do
  retries=$((retries - 1))
  if [ "$retries" -le 0 ]; then
    echo "RoadRunner HTTP runtime did not become ready. Last container logs:" >&2
    COMPOSE_PROFILES=performance \
    COMPOSE_PROJECT_NAME="$project_name" \
    ROADRUNNER_PORT="$port" \
      docker compose -f docker-compose.yml -f "$override_file" logs --no-color --tail=120 checkout-roadrunner >&2 || true
    exit 1
  fi
  sleep 1
done

echo "RoadRunner HTTP runtime smoke test passed."
