# Branching Strategy

Use trunk-based development with short-lived branches.

## Branches

- `main`: protected, always runnable.
- `feature/*`: product or platform features.
- `fix/*`: bug fixes.
- `docs/*`: documentation-only work.
- `experiment/*`: isolated spikes such as HTTP/3 benchmarking.
- `agent/*`: isolated parallel agent work, for example `agent/outbox-publisher`.

## Orchestrator Branches

When foreground work is needed, the lead orchestrator assigns it to a named worker. Relay creates or switches to a short-scoped branch for integration work, and implementation workers may use `agent/<short-scope>` for agent-owned implementation or investigation work. Use `docs/<short-scope>` for documentation-only changes.

Examples:

- `agent/outbox-publisher`
- `agent/roadrunner-runtime`
- `docs/git-workflow`

## Agent Branches

Parallel worker agents may create `agent/<short-scope>` branches when the lead orchestrator assigns independent work. These branches are for isolation and speed, not long-lived ownership.

Rules:

- Keep each `agent/*` branch limited to the assigned files and acceptance criteria.
- Do not edit files owned by another active agent branch.
- Push only to GitLab `origin`.
- Report changed paths, validation, and integration notes back to Relay and the orchestrator.
- Integrate through Relay review, cherry-pick, merge, or a GitLab merge request.
- Delete stale `agent/*` branches after integration or abandonment.

## Merge Requests

Agents may create GitLab merge requests targeting `main` when the user asks and an approved tool/token is available. Prefer `make create-auto-merge-mr` so squash, source-branch deletion, and auto-merge are set consistently. Final merge remains governed by GitLab checks and branch rules.

Merge request rules:

- Target `main` unless the user names another target branch.
- Use a concise title that can become the squash commit subject.
- Include a description with summary, validation, risk, and manual follow-up.
- Enable squash-on-merge where possible.
- Provide a clean squash commit message for the final merge commit.
- Enable source branch deletion on merge.
- Enable auto-merge once checks pass when the user has authorized agent MR creation.
- Ask the user to review the MR and pipeline result.

## Commit Messages

Use clean imperative commit messages that explain the change. Avoid generic tool-generated messages like `Added by cursor`.

Rules:

- Use an imperative subject, ideally 50 characters or fewer.
- Make one commit per coherent behavior, guardrail, runtime, or documentation slice.
- Split unrelated docs, implementation, tests, and CI changes unless they are part of one atomic change.
- Add a body only for motivation, tradeoffs, validation, or follow-up risk.
- Do not mention vendor inspiration or competitor names in commit messages.
- When preparing a merge request, set the squash commit message separately from the branch commits.

Examples:

- `Add Phase 1 agent roster`
- `Document GitLab mirror workflow`
- `Refine local tooling guidance`
- `Enable outbox stream publisher`
- `Harden Markdown link checks`

## Git Flow

Full Git Flow is not the default. Add `develop`, `release/*`, or `hotfix/*` only if release cadence later requires it.
