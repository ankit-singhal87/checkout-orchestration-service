#!/usr/bin/env sh
set -eu

feature_dir="apps/checkout/tests/Behavior/features"

if [ ! -d "$feature_dir" ]; then
  echo "Missing behavior feature directory: $feature_dir" >&2
  exit 1
fi

feature_count=$(find "$feature_dir" -name "*.feature" -type f | wc -l | tr -d " ")

if [ "$feature_count" -lt 5 ]; then
  echo "Expected at least 5 behavior feature files, found $feature_count" >&2
  exit 1
fi

missing_keyword=0

for feature in "$feature_dir"/*.feature; do
  if ! grep -q "^Feature:" "$feature"; then
    echo "Missing Feature header: $feature" >&2
    missing_keyword=1
  fi

  if ! grep -q "Scenario:" "$feature"; then
    echo "Missing Scenario: $feature" >&2
    missing_keyword=1
  fi
done

if [ "$missing_keyword" -ne 0 ]; then
  exit 1
fi

echo "Behavior specs validation passed."
