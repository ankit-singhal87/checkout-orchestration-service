#!/usr/bin/env sh
set -eu

base_ref="${MIGRATION_IMMUTABILITY_BASE_REF:-origin/main}"

sh scripts/git/check-migration-immutability.sh "$base_ref"
sh scripts/ci/validate-phase1.sh

if [ "${RUN_CHECKOUT_TESTS_ON_PRE_PUSH:-0}" = "1" ]; then
  sh scripts/test/checkout-app.sh
else
  echo "Skipping checkout app tests. Set RUN_CHECKOUT_TESTS_ON_PRE_PUSH=1 to run them before push."
fi
