#!/usr/bin/env sh
set -eu

base_ref="${MIGRATION_IMMUTABILITY_BASE_REF:-${1:-origin/main}}"
migration_path="apps/checkout/database/migrations"

if ! git rev-parse --verify "$base_ref" >/dev/null 2>&1; then
  echo "Migration immutability base ref not found: $base_ref" >&2
  echo "Set MIGRATION_IMMUTABILITY_BASE_REF or pass a base ref explicitly." >&2
  exit 1
fi

merge_base="$(git merge-base "$base_ref" HEAD)"

violations="$(
  git diff --name-status "$merge_base"...HEAD -- "$migration_path" \
    | awk '$1 ~ /^[MD]$/ { print }'
)"

if [ -n "$violations" ]; then
  echo "Tracked migration files must not be modified or deleted after they may have run." >&2
  echo "Add a new migration instead." >&2
  echo "$violations" >&2
  exit 1
fi

echo "Migration immutability validation passed."
