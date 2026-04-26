#!/usr/bin/env sh
set -eu

if docker compose version >/dev/null 2>&1; then
  docker compose down
elif command -v docker-compose >/dev/null 2>&1; then
  docker-compose down
else
  echo "Docker Compose is required." >&2
  exit 1
fi

echo "Local Phase 0 infrastructure stopped."
