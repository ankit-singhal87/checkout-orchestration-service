#!/usr/bin/env sh
set -eu

fixture_dir="seed/fixtures"

if [ ! -d "$fixture_dir" ]; then
  echo "Missing seed fixture directory: $fixture_dir" >&2
  exit 1
fi

for path in "$fixture_dir"/*.json; do
  if [ ! -f "$path" ]; then
    echo "No seed fixture JSON files found in $fixture_dir." >&2
    exit 1
  fi
done

if command -v jq >/dev/null 2>&1; then
  for path in "$fixture_dir"/*.json; do
    jq empty "$path"
  done
  echo "Seed fixture JSON validation passed."
else
  echo "jq is not installed; seed fixture JSON syntax check skipped."
fi
