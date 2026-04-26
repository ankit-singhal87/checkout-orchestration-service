# Repository Workflow

GitLab is the primary repository. GitHub is a public portfolio mirror updated by GitLab repository mirroring.

- Create branches from the GitLab repository.
- Push only to GitLab `origin`.
- Create merge requests manually in GitLab.
- Merge manually in GitLab.
- Do not push to the `github` remote unless repairing the mirror.

## GitHub Mirror

GitHub should not own issues, merge requests, releases, deployments, or protected branch policy.

GitHub Actions, if enabled, should stay lightweight: scaffold validation, link checks, and public smoke checks only. Do not deploy from GitHub Actions.
