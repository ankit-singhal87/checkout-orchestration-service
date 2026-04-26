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

- Every non-read-only worker must use a short-scoped branch before editing files, generating tracked artifacts, coordinating release mechanics, committing, or pushing.
- Parallel non-read-only workers must use separate worktrees so local state, generated files, and validation outputs do not collide.
- Read-only advisory workers may inspect the current worktree without their own branch when they do not edit files or run commands that change tracked artifacts.
- Keep each `agent/*` branch limited to the assigned files and acceptance criteria.
- Do not edit files owned by another active agent branch.
- Push only to GitLab `origin`.
- Report changed paths, validation, and integration notes back to Relay and the orchestrator.
- Integrate through Relay review, cherry-pick, merge, or a GitLab merge request.
- Once a stable slice is validated, move it quickly to Relay for commit and, when authorized, merge request preparation. Do not let validated local work sit while unrelated context accumulates.
- Mutually exclusive independent tasks may use multiple Relay lanes in parallel when the Conductor assigns distinct branches, worktrees, writable file sets, and validation scopes.
- Read-only Observer lanes may run alongside each Relay lane to report branch, MR, merge, CI, and pipeline status. Observer must not resolve conflicts, commit, push, prepare MRs, or do other Relay work.
- If Observer reports a failed check, blocked merge, conflict, or unclear branch state, the Conductor decides whether to start or assign Relay for integration work.
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

Use clean imperative commit messages that explain the change. Every commit subject must start with one of the active category prefixes:

- `func:` product or user-visible behavior, API behavior, checkout behavior, or user flows.
- `tech:` platform, runtime, infrastructure, dependencies, refactors, tests, CI, or internal implementation with no direct product behavior change.
- `agentic:` agent operating model, orchestration, handoffs, worker definitions, or automation for agent workflows.

The active standard is `func:`, `tech:`, and `agentic:`. Shorter alternatives such as `feat:`, `chore:`, and `agent:` are familiar, but this repository uses the requested requirement-oriented categories so commit history shows whether the dominant outcome is functional, technical, or agent-process related.

Rules:

- Use an imperative subject, ideally 50 characters or fewer.
- Put the category prefix first, then an imperative subject.
- Make one commit per coherent behavior, guardrail, runtime, or documentation slice.
- Choose the prefix by outcome, not file type. Documentation can be `func:`, `tech:`, or `agentic:` depending on the decision it records.
- For ambiguous mixed commits, split the changes when they have distinct category-level outcomes; otherwise choose the prefix that describes the dominant externally useful outcome.
- Split commits when one change has multiple category-level outcomes, such as checkout behavior plus unrelated CI hardening.
- Keep tests with the behavior or implementation they prove; use `tech:` when the commit is only test infrastructure or coverage with no product behavior change.
- Add a body only for motivation, tradeoffs, validation, or follow-up risk.
- Do not mention vendor inspiration or competitor names in commit messages.
- When preparing a merge request, set the squash commit message separately from the branch commits.

Examples:

- `func: Add checkout conflict response`
- `func: Show checkout confirmation page`
- `tech: Harden Markdown link checks`
- `tech: Add RoadRunner runtime guardrail`
- `agentic: Define narrow agent template`
- `agentic: Document GitLab mirror workflow`

## Git Flow

Full Git Flow is not the default. Add `develop`, `release/*`, or `hotfix/*` only if release cadence later requires it.
