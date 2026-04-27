# Repository Workflow

GitLab is the primary repository. GitHub is a public portfolio mirror updated by GitLab repository mirroring.

- Create branches from the GitLab repository.
- Push only to GitLab `origin`.
- Create merge requests in GitLab when the user asks and the local `glab` session is API-authenticated; otherwise ask the user to create them manually.
- Prefer `make create-auto-merge-mr` for agent-created MRs. It pushes the current branch, creates or reuses a GitLab MR targeting `main`, enables squash, requests source-branch deletion, and enables auto-merge once checks pass.
- After MR creation or merge, update [context-handoff.md](../../docs/agent/context-handoff.md) only with durable 1-2 line learnings needed by the next agent session.
- Final merge still depends on GitLab branch rules and successful checks. Do not manually force-merge failed pipelines.
- Do not push to the `github` remote unless repairing the mirror.

Merge requests should target `main`, use squash-on-merge, carry a clean squash commit message, remove the source branch on merge, and use auto-merge when the user has authorized agent MR creation. GitHub remains a mirror and should not receive pull requests for normal project work.

## GitHub Mirror

GitHub should not own issues, merge requests, releases, deployments, or protected branch policy.

GitHub Actions, if enabled, should stay lightweight: scaffold validation, link checks, and public smoke checks only. Do not deploy from GitHub Actions.
