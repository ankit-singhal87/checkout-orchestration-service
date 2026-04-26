#!/usr/bin/env sh
set -eu

required="git docker"
missing=0

for tool in $required; do
  if ! command -v "$tool" >/dev/null 2>&1; then
    echo "Missing required tool: $tool" >&2
    missing=1
  else
    echo "Found $tool: $(command -v "$tool")"
  fi
done

if docker compose version >/dev/null 2>&1; then
  echo "Found Docker Compose plugin"
elif command -v docker-compose >/dev/null 2>&1; then
  echo "Found docker-compose"
else
  echo "Missing required tool: Docker Compose" >&2
  missing=1
fi

optional="php composer go node npm terraform kubectl kind k3d protoc glab gh"

for tool in $optional; do
  if command -v "$tool" >/dev/null 2>&1; then
    echo "Found optional tool: $tool"
  else
    echo "Optional tool not installed yet: $tool"
  fi
done

if [ "$missing" -ne 0 ]; then
  exit 1
fi

echo "Required Phase 0 tools are available."
