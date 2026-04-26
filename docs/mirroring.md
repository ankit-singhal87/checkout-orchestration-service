# GitLab To GitHub Mirroring

GitLab is the primary public repository. GitHub is a public read-only mirror.

## Recommended Setup

Configure repository mirroring from GitLab to GitHub using GitLab's repository mirror settings.

GitHub should not own:

- issues
- merge requests
- releases
- deployments
- protected branch policy

## GitHub Actions

GitHub Actions should remain lightweight:

- validate scaffold
- check links later
- run public smoke checks later

Do not deploy from GitHub Actions.