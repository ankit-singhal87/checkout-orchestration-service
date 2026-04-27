#!/usr/bin/env sh
set -eu

app_dir="apps/checkout"
project_name="${WORKER_RUNTIME_PROJECT:-checkout-worker-smoke}"
override_file="${TMPDIR:-/tmp}/checkout-worker-smoke.$$.yml"
stream="${REDIS_STREAM:-checkout:events}"
event_id="worker-smoke-$(date +%s)-$$"

cleanup() {
  COMPOSE_PROFILES=worker \
  COMPOSE_PROJECT_NAME="$project_name" \
    docker compose -f docker-compose.yml -f "$override_file" down -v --remove-orphans >/dev/null 2>&1 || true
  rm -f "$override_file"
}
trap cleanup EXIT INT TERM

if [ ! -f "$app_dir/artisan" ]; then
  echo "Laravel app is not generated yet; skipping worker runtime smoke test."
  exit 0
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required for the worker runtime smoke test." >&2
  exit 1
fi

cat >"$override_file" <<'YAML'
services:
  mysql:
    ports: !reset []
  redis:
    ports: !reset []
  checkout-outbox-worker:
    environment:
      OUTBOX_WORKER_INTERVAL_SECONDS: "1"
      OUTBOX_WORKER_LIMIT: "10"
YAML

if [ ! -f "$app_dir/vendor/autoload.php" ]; then
  COMPOSE_PROFILES=worker \
  COMPOSE_PROJECT_NAME="$project_name" \
    docker compose -f docker-compose.yml -f "$override_file" build checkout-outbox-worker

  COMPOSE_PROFILES=worker \
  COMPOSE_PROJECT_NAME="$project_name" \
    docker compose -f docker-compose.yml -f "$override_file" run --rm --no-deps --entrypoint composer checkout-outbox-worker install --no-interaction --prefer-dist
fi

COMPOSE_PROFILES=worker \
COMPOSE_PROJECT_NAME="$project_name" \
  docker compose -f docker-compose.yml -f "$override_file" up -d --build checkout-outbox-worker

retries=60
until COMPOSE_PROFILES=worker COMPOSE_PROJECT_NAME="$project_name" docker compose -f docker-compose.yml -f "$override_file" exec -T mysql mysqladmin ping -h 127.0.0.1 -u root -pcheckout_root --silent >/dev/null 2>&1; do
  retries=$((retries - 1))
  if [ "$retries" -le 0 ]; then
    echo "MySQL did not become ready for worker runtime smoke test." >&2
    exit 1
  fi
  sleep 1
done

retries=60
tenant_id=""
while [ "$retries" -gt 0 ]; do
  tenant_id="$(COMPOSE_PROFILES=worker COMPOSE_PROJECT_NAME="$project_name" docker compose -f docker-compose.yml -f "$override_file" exec -T mysql mysql -N -B -u root -pcheckout_root checkout -e "SELECT id FROM tenants ORDER BY id LIMIT 1;" 2>/dev/null || true)"
  if [ -n "$tenant_id" ]; then
    break
  fi
  retries=$((retries - 1))
  sleep 1
done

if [ -z "$tenant_id" ]; then
  echo "Worker runtime smoke test could not find a seeded tenant." >&2
  COMPOSE_PROFILES=worker COMPOSE_PROJECT_NAME="$project_name" docker compose -f docker-compose.yml -f "$override_file" logs --no-color --tail=120 checkout-outbox-worker >&2 || true
  exit 1
fi

COMPOSE_PROFILES=worker \
COMPOSE_PROJECT_NAME="$project_name" \
  docker compose -f docker-compose.yml -f "$override_file" exec -T mysql mysql -u root -pcheckout_root checkout -e "
INSERT INTO outbox_events (
  tenant_record_id,
  event_id,
  event_type,
  aggregate_type,
  aggregate_id,
  payload,
  created_at,
  updated_at
) VALUES (
  ${tenant_id},
  '${event_id}',
  'worker.runtime.smoke',
  'worker-smoke',
  '${event_id}',
  JSON_OBJECT('source', 'scripts/test/worker-runtime.sh'),
  NOW(),
  NOW()
);
"

retries=30
until COMPOSE_PROFILES=worker COMPOSE_PROJECT_NAME="$project_name" docker compose -f docker-compose.yml -f "$override_file" exec -T mysql mysql -N -B -u root -pcheckout_root checkout -e "SELECT COUNT(*) FROM outbox_events WHERE event_id = '${event_id}' AND published_at IS NOT NULL;" | grep -qx '1'; do
  retries=$((retries - 1))
  if [ "$retries" -le 0 ]; then
    echo "Worker did not publish smoke outbox event. Last worker logs:" >&2
    COMPOSE_PROFILES=worker COMPOSE_PROJECT_NAME="$project_name" docker compose -f docker-compose.yml -f "$override_file" logs --no-color --tail=120 checkout-outbox-worker >&2 || true
    exit 1
  fi
  sleep 1
done

if ! COMPOSE_PROFILES=worker COMPOSE_PROJECT_NAME="$project_name" docker compose -f docker-compose.yml -f "$override_file" exec -T redis redis-cli XRANGE "$stream" - + COUNT 50 | grep -q "$event_id"; then
  echo "Worker marked the event published, but Redis Stream ${stream} did not contain ${event_id}." >&2
  exit 1
fi

echo "Worker runtime smoke test published ${event_id} to ${stream}."
