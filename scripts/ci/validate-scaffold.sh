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
wiki/roadmap/checkout-mvp-plan.md
docs/agent/agents.md
wiki/status/phase-0-risk-register.md
wiki/runbooks/ai-tooling.md
wiki/runbooks/gitlab-token-usage.md
wiki/runbooks/repository-mirroring.md
wiki/runbooks/local-tools.md
wiki/runbooks/debugging.md
wiki/architecture/README.md
wiki/adr/README.md
docker-compose.yml
observability/otel-collector.yml
observability/prometheus.yml
observability/loki.yml
observability/tempo.yml
scripts/dev/check-tools.sh
scripts/dev/install-host-tools.sh
scripts/dev/up.sh
scripts/dev/down.sh
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
