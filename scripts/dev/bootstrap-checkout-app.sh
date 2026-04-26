#!/usr/bin/env sh
set -eu

app_dir="apps/checkout"
tmp_dir=".tmp/laravel-checkout"

if [ -f "$app_dir/artisan" ]; then
  echo "Laravel checkout app already exists at $app_dir."
  exit 0
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker CLI is required to bootstrap the checkout app." >&2
  exit 1
fi

if ! docker info >/dev/null 2>&1; then
  echo "Docker daemon is not reachable by this user." >&2
  echo "Ensure Docker is running and that your user can access /var/run/docker.sock." >&2
  exit 1
fi

mkdir -p ".tmp"
rm -rf "$tmp_dir"

echo "Building checkout PHP/Composer image..."
docker build -t checkout-orchestration-checkout:bootstrap -f docker/checkout.Dockerfile .

echo "Creating Laravel skeleton in a temporary directory using the checkout image..."
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$PWD/.tmp:/workspace" \
  -w /workspace \
  checkout-orchestration-checkout:bootstrap \
  composer create-project laravel/laravel laravel-checkout --no-interaction

echo "Copying Laravel skeleton into $app_dir without removing existing docs..."
if [ -f "$app_dir/README.md" ] && [ -f "$tmp_dir/README.md" ]; then
  mv "$tmp_dir/README.md" "$tmp_dir/LARAVEL.md"
fi
cp -R "$tmp_dir"/. "$app_dir"/
rm -rf "$tmp_dir"

echo "Laravel checkout app bootstrapped."
echo "Review generated files before committing."
