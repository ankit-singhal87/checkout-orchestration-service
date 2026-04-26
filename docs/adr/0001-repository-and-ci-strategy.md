# ADR 0001: Repository And CI Strategy

## Status

Accepted

## Decision

Use a public GitLab repository as the primary source of truth and GitLab CI/CD as the primary pipeline. Use a public GitHub repository as a read-only mirror with lightweight validation only.

## Consequences

- GitLab owns merge requests, issues, releases, and deploy workflows.
- GitHub improves discoverability for demo audiences.
- Shared scripts under `scripts/ci` reduce pipeline drift.
- GitHub Actions must not own deployments or releases.