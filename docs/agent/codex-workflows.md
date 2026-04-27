# Codex Workflows

`codex-workflows` is installed as a repo-local Codex tooling layer. It provides
generic recipe skills under `.agents/skills` and subagent definitions under
`.codex/agents`.

Repository-local rules remain authoritative. If an upstream recipe conflicts
with [../../AGENTS.md](../../AGENTS.md), [agents.md](agents.md), phase
boundaries, GitLab-first workflow, named project-agent ownership, or
[validation.md](validation.md), follow the repository rule.

## Recipe Routing

| Recipe | Default use in this repo | Guardrail |
| --- | --- | --- |
| `$recipe-plan` | Planning, task slicing, acceptance criteria, and advisory work. | Read-only unless the user or `lead-orchestrator` assigns a branch and editable paths. |
| `$recipe-task` | Convert scoped intent into a work package. | Must include allowed paths, validation commands, and stop conditions before edits. |
| `$recipe-implement` | TDD or bounded implementation by a named worker. | Must respect named-agent ownership and branch/worktree routing. |
| `$recipe-fullstack-implement` | Cross-layer implementation only after the repo slice is explicitly scoped. | Stop if it crosses Laravel, runtime, docs, and contract lanes without assignment. |
| `$recipe-review` | Read-only review for defects, missing tests, security risks, and doc drift. | Findings first; no file edits unless a follow-up edit task is assigned. |
| `$recipe-diagnose` | Blocker investigation and root-cause analysis. | Prefer read-only diagnosis; hand implementation back to the owning worker or Relay. |
| `$recipe-update-doc` | Bounded documentation updates. | Do not edit upstream-managed `.agents` or `.codex` files. |
| `$recipe-reverse-engineer` | Understanding unfamiliar code, contracts, or runtime behavior. | Read-only by default. |

## Artifact Routing

Recipe-generated planning artifacts may use upstream-standard paths:

- `docs/prd`
- `docs/design`
- `docs/adr`
- `docs/ui-spec`
- `docs/plans/tasks`

Use those paths for draft or recipe-native artifacts. Move durable repository
policy into existing repo-owned docs when it becomes authoritative:

- Architecture, ADR narrative, runbooks, status, and roadmap: `wiki`
- Agent operations: `docs/agent`
- Compact cross-session handoff: `docs/agent/context-handoff.md`
- Public contracts: `docs/contracts` and `docs/api`
- Implementation standards: `docs/standards`

## Unattended Development

Development may run unattended only inside an explicit maintenance loop for a
specific branch or merge request. The user must approve that loop before it
starts.

Allowed unattended work:

- Poll CI, merge request status, and validation output.
- Apply focused fixes inside assigned paths.
- Rerun approved validation commands.
- Commit and push when the user has authorized that branch workflow.
- Update handoff context with concise durable facts.

Stop and ask before:

- Dependency changes.
- Secret handling.
- Destructive Git operations.
- Paid cloud or deploy work.
- Scope changes outside assigned paths.
- Product decisions or contract changes not already accepted.
- Final merge.

Unattended mode still requires a branch, allowed paths, validation commands,
and stop conditions. It is not permission for open-ended development.

## Managed Files

Do not hand-edit upstream-managed files unless the task is explicitly to carry a
local patch:

- `.agents/skills`
- `.codex/agents`
- `.codex-workflows-manifest.json`

Before updating managed files, run:

```sh
make codex-workflows-update-dry-run
```

Apply updates with:

```sh
make codex-workflows-update
```
