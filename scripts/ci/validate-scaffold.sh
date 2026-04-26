#!/usr/bin/env sh
set -eu

required_files="
README.md
AGENTS.md
.editorconfig
.gitignore
.env.example
.gitlab-ci.yml
.github/workflows/mirror-validation.yml
docs/planning/checkout-mvp-plan.md
docs/phase-0-risk-register.md
docs/ai-tooling.md
docs/branching-strategy.md
docs/gitlab-token-usage.md
docs/mirroring.md
docs/local-tools.md
docs/debugging.md
docs/architecture/README.md
docs/adr/README.md
docker-compose.yml
observability/otel-collector.yml
observability/prometheus.yml
scripts/dev/check-tools.sh
scripts/dev/up.sh
scripts/dev/down.sh
"

missing=0

for file in $required_files; do
  if [ ! -f "$file" ]; then
    echo "Missing required scaffold file: $file" >&2
    missing=1
  fi
done

if [ "$missing" -ne 0 ]; then
  exit 1
fi

if git ls-files | grep -E '(^|/)(\.env|.*\.pem|.*\.key|credentials\.json|secrets\.json)$' >/dev/null 2>&1; then
  echo "Tracked secret-like file detected." >&2
  exit 1
fi

echo "Phase 0 scaffold validation passed."
