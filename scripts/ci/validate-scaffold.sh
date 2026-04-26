#!/usr/bin/env sh
set -eu

required_files="
README.md
AGENTS.md
Makefile
.editorconfig
.gitignore
.env.example
.gitlab-ci.yml
.github/workflows/mirror-validation.yml
docs/human/planning/checkout-mvp-plan.md
docs/agent/agents.md
docs/agent/context-handoff.md
docs/human/phase-0-risk-register.md
docs/agent/ai-tooling.md
docs/agent/branching-strategy.md
docs/agent/gitlab-token-usage.md
docs/agent/mirroring.md
docs/agent/local-tools.md
docs/agent/debugging.md
docs/human/architecture/README.md
docs/human/adr/README.md
docker-compose.yml
observability/otel-collector.yml
observability/prometheus.yml
observability/loki.yml
observability/tempo.yml
scripts/dev/check-tools.sh
scripts/dev/install-host-tools.sh
scripts/dev/up.sh
scripts/dev/down.sh
scripts/agent/defragment-context.sh
scripts/git/create-auto-merge-mr.sh
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
