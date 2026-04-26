# GitLab Token Usage

The `cursor-dev-agent-git` token is for local Git over HTTPS only.

## Allowed Use

- Clone from GitLab.
- Pull from GitLab.
- Push branches to GitLab.

## Not Allowed

- Do not use it for GitLab API automation.
- Do not use it for registry pushes.
- Do not use it for CI variables.
- Do not use it for runner management.
- Do not use it for deploys.
- Do not commit it to this repository.

## Manual Merge Request Flow

For now, agents push branches and the user creates merge requests manually in GitLab.

```bash
git push -u origin docs/phase-0-scaffolding
```

Then create the merge request in the GitLab UI.