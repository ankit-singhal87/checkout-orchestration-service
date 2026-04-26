# ADR 0001: Repository And CI Strategy

## Status

Accepted

## Decision

Use GitLab as the source of truth, write target, review target, and CI/CD host. Use GitHub only as a public portfolio mirror maintained by GitLab repository mirroring.

## Consequences

- GitLab owns merge requests, issues, releases, and deploy workflows.
- Agents and local development push only to GitLab.
- GitHub improves discoverability without becoming a second collaboration workflow.
- Pull requests and merges are manual.