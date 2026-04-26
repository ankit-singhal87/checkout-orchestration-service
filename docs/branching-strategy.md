# Branching Strategy

Use trunk-based development with short-lived branches.

## Branches

- `main`: protected, always runnable.
- `feature/*`: product or platform features.
- `fix/*`: bug fixes.
- `docs/*`: documentation-only work.
- `experiment/*`: isolated spikes such as HTTP/3 benchmarking.

## Merge Requests

Merge requests and merges are manual GitLab steps. Agents may push branches to GitLab when asked.

## Commit Messages

Use short, descriptive commit messages that explain the change. Avoid generic tool-generated messages like `Added by cursor`.

Examples:

- `Add Phase 1 agent roster`
- `Document GitLab mirror workflow`
- `Refine local tooling guidance`

## Git Flow

Full Git Flow is not the default. Add `develop`, `release/*`, or `hotfix/*` only if release cadence later requires it.