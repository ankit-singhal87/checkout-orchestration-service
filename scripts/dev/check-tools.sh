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

recommended="php composer node npm make curl jq openssl"

for tool in $recommended; do
  if command -v "$tool" >/dev/null 2>&1; then
    echo "Found recommended tool: $tool"
  else
    echo "Recommended host tool not installed yet: $tool"
  fi
done

optional="mysql redis-cli go terraform kubectl kind k3d protoc glab gh"

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

echo "Required Phase 1 host tools are available."
echo "Service dependencies are expected to run through Docker Compose."
