#!/usr/bin/env sh
set -eu

if [ ! -f ".env" ]; then
  echo "No .env found. Copying .env.example for local defaults."
  cp .env.example .env
fi

if docker compose version >/dev/null 2>&1; then
  docker compose up -d
elif command -v docker-compose >/dev/null 2>&1; then
  docker-compose up -d
else
  echo "Docker Compose is required." >&2
  exit 1
fi

echo "Local development services are starting."
echo "Use COMPOSE_PROFILES=app sh scripts/dev/up.sh to include the checkout container."
echo "Use COMPOSE_PROFILES=search,observability,identity sh scripts/dev/up.sh for optional support services."
