# GitLab Token Usage

The local agent Git token is for local Git over HTTPS only.

## Allowed Use

- Clone from GitLab.
- Pull from GitLab.
- Push branches to GitLab.
- Create merge requests only if the user explicitly authorizes using an API-capable token or authenticated CLI for that purpose.

## Not Allowed

- Do not use the Git-only token for GitLab API automation.
- Do not use it for registry pushes.
- Do not use it for CI variables.
- Do not use it for runner management.
- Do not use it for deploys.
- Do not commit it to this repository.

## Merge Request Flow

Agent-created merge requests are enabled for this project once the local environment has an API-capable authenticated `glab` session. Until then, agents push branches and ask the user to create merge requests manually in GitLab.

```bash
git push -u origin docs/phase-0-scaffolding
```

Use a separate API-capable credential or an authenticated `glab` session. Do not reuse the Git-only token.

Required MR settings:

- Target branch: `main`.
- Squash commits on merge.
- Clean squash commit message, separate from individual branch commit messages.
- No automatic merge by the agent.
