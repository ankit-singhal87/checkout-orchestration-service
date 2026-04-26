#!/usr/bin/env sh
set -eu

app_dir="apps/checkout"
port="${CHECKOUT_PARITY_PORT:-18443}"
project_name="${CHECKOUT_PARITY_PROJECT:-checkout-parity-smoke}"
curl_image="${CHECKOUT_HTTP3_CURL_IMAGE:-alpine/curl-http3:latest}"
override_file="${TMPDIR:-/tmp}/checkout-parity-smoke.$$.yml"
scratch_dir="${TMPDIR:-/tmp}/checkout-parity-smoke.$$"

cleanup() {
  COMPOSE_PROFILES=app,parity \
  COMPOSE_PROJECT_NAME="$project_name" \
  PARITY_HTTPS_PORT="$port" \
    docker compose -f docker-compose.yml -f docker-compose.parity.yml -f "$override_file" down -v --remove-orphans >/dev/null 2>&1 || true
  rm -f "$override_file"
  rm -rf "$scratch_dir"
}
trap cleanup EXIT INT TERM

if [ ! -f "$app_dir/artisan" ]; then
  echo "Laravel app is not generated yet; skipping checkout parity smoke test."
  exit 0
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required for the checkout parity smoke test." >&2
  exit 1
fi

mkdir -p "$scratch_dir"
chmod 0777 "$scratch_dir"

cat >"$override_file" <<'YAML'
services:
  mysql:
    ports: !reset []
  redis:
    ports: !reset []
YAML

if [ ! -f "$app_dir/vendor/autoload.php" ]; then
  COMPOSE_PROFILES=app,parity \
  COMPOSE_PROJECT_NAME="$project_name" \
  PARITY_HTTPS_PORT="$port" \
    docker compose -f docker-compose.yml -f docker-compose.parity.yml -f "$override_file" build checkout

  COMPOSE_PROFILES=app,parity \
  COMPOSE_PROJECT_NAME="$project_name" \
  PARITY_HTTPS_PORT="$port" \
    docker compose -f docker-compose.yml -f docker-compose.parity.yml -f "$override_file" run --rm --no-deps --entrypoint composer checkout install --no-interaction --prefer-dist
fi

COMPOSE_PROFILES=app,parity \
COMPOSE_PROJECT_NAME="$project_name" \
PARITY_HTTPS_PORT="$port" \
  docker compose -f docker-compose.yml -f docker-compose.parity.yml -f "$override_file" up -d --build checkout-proxy

proxy_container_id="$(
  COMPOSE_PROFILES=app,parity \
  COMPOSE_PROJECT_NAME="$project_name" \
  PARITY_HTTPS_PORT="$port" \
    docker compose -f docker-compose.yml -f docker-compose.parity.yml -f "$override_file" ps -q checkout-proxy
)"
proxy_ip="$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "$proxy_container_id")"

if ! docker run --rm "$curl_image" curl -V | grep -q "HTTP3"; then
  echo "The configured curl image must support HTTP/3: $curl_image" >&2
  exit 1
fi

if ! grep -Eq "protocols[[:space:]]+h1[[:space:]]+h2[[:space:]]+h3" infra/local/caddy/Caddyfile; then
  echo "Parity Caddy config must explicitly allow HTTP/1.1, HTTP/2, and HTTP/3." >&2
  exit 1
fi

if ! COMPOSE_PROFILES=app,parity \
  COMPOSE_PROJECT_NAME="$project_name" \
  PARITY_HTTPS_PORT="$port" \
  docker compose -f docker-compose.yml -f docker-compose.parity.yml -f "$override_file" config |
  grep -A4 "target: 443" | grep -q "protocol: udp"; then
  echo "Parity Compose config must publish UDP 443 for HTTP/3." >&2
  exit 1
fi

curl_parity() {
  docker run --rm \
    --network "${project_name}_default" \
    "$curl_image" \
    curl \
    --resolve "fashion-demo.localhost:443:$proxy_ip" \
    "$@"
}

retries=30
until curl_parity -kfsS "https://fashion-demo.localhost/shop" >/dev/null 2>&1; do
  retries=$((retries - 1))
  if [ "$retries" -le 0 ]; then
    echo "Checkout parity proxy did not become ready. Last container logs:" >&2
    COMPOSE_PROFILES=app,parity \
    COMPOSE_PROJECT_NAME="$project_name" \
    PARITY_HTTPS_PORT="$port" \
      docker compose -f docker-compose.yml -f docker-compose.parity.yml -f "$override_file" logs --no-color --tail=160 checkout-proxy nginx checkout >&2 || true
    exit 1
  fi
  sleep 1
done

assert_shop_protocol() {
  protocol_name="$1"
  expected_version="$2"
  curl_flag="$3"

  set -- $(
    curl_parity "$curl_flag" -kfsS -o /dev/null -w "%{http_code} %{http_version}" \
      "https://fashion-demo.localhost/shop"
  )
  status_code="$1"
  http_version="$2"

  if [ "$status_code" != "200" ]; then
    echo "Expected $protocol_name parity shop response status 200, got $status_code." >&2
    exit 1
  fi

  if [ "$http_version" != "$expected_version" ]; then
    echo "Expected parity proxy to negotiate $protocol_name, got HTTP/$http_version." >&2
    exit 1
  fi
}

assert_shop_protocol "HTTP/1.1" "1.1" "--http1.1"
assert_shop_protocol "HTTP/2" "2" "--http2"
assert_shop_protocol "HTTP/3" "3" "--http3-only"

headers="$(
  curl_parity --http2 -kfsS -D - -o /dev/null "https://fashion-demo.localhost/shop"
)"

body="$(
  curl_parity --http2 -kfsS "https://fashion-demo.localhost/shop"
)"

for header_name in \
  "strict-transport-security" \
  "x-content-type-options" \
  "x-frame-options" \
  "referrer-policy" \
  "permissions-policy"; do
  if ! printf '%s\n' "$headers" | grep -iq "^$header_name:"; then
    echo "Missing parity security header: $header_name" >&2
    exit 1
  fi
done

if printf '%s\n' "$headers" | grep -iq "^server:"; then
  echo "Parity proxy should not expose a Server response header." >&2
  exit 1
fi

if ! printf '%s\n' "$body" | grep -q "Fashion Store"; then
  echo "Parity proxy did not return the fashion tenant shop page." >&2
  exit 1
fi

large_status="$(
  docker run --rm \
    --network "${project_name}_default" \
    "$curl_image" \
    sh -c 'dd if=/dev/zero bs=1024 count=2050 2>/dev/null | curl --resolve "fashion-demo.localhost:443:$0" -ksS -o /dev/null -w "%{http_code}" -X POST --data-binary @- "https://fashion-demo.localhost/cart/items"' \
    "$proxy_ip" || true
)"

if [ "$large_status" != "413" ]; then
  echo "Expected parity proxy to reject oversized requests with 413, got $large_status." >&2
  exit 1
fi

echo "Checkout parity smoke test passed."
