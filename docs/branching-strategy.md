# Branching Strategy

Use trunk-based development with short-lived branches.

## Branches

- `main`: protected, always runnable.
- `feature/*`: product or platform features.
- `fix/*`: bug fixes.
- `docs/*`: documentation-only work.
- `experiment/*`: isolated spikes such as HTTP/3 benchmarking.

## Merge Requests

Merge requests are created manually in GitLab for now.

Agents may push branches if Git credentials are available, but should not create merge requests automatically until a separate GitLab API token exists.

## Git Flow

Full Git Flow is not the default. Add `develop`, `release/*`, or `hotfix/*` only if release cadence later requires it.