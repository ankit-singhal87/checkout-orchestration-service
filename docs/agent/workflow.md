# Agent Workflow

1. Read [docs/agent/README.md](README.md).
2. Inspect only the files relevant to the task.
3. Check current worktree status; do not revert edits made by others.
4. Make focused changes inside the assigned ownership scope.
5. Run the cheapest relevant validation from [validation.md](validation.md).
6. Update agent docs if operational facts changed.
7. Update human docs only if reviewer-facing behavior or architecture changed.
8. Summarize diff, validation, risks, and any intentionally unchanged mixed docs.

## Codex Workflows Integration

`codex-workflows` recipes and subagents are installed as a reusable Codex
tooling layer under `.agents/skills` and `.codex/agents`. They can be invoked
with recipe skills such as `$recipe-implement`, `$recipe-task`,
`$recipe-review`, and `$recipe-diagnose` when the task benefits from structured
requirements, design, TDD implementation, or quality gates.

Repository-local instructions remain authoritative. The root
[../../AGENTS.md](../../AGENTS.md), this `docs/agent` tree, GitLab-first branch
workflow, phase boundaries, named project-agent ownership, and validation
matrix override generic upstream recipe behavior when they differ.

Recipe-generated planning artifacts may use the upstream-standard paths
`docs/prd`, `docs/design`, `docs/adr`, `docs/ui-spec`, and `docs/plans/tasks`.
Long-lived project architecture, ADRs, and planning context should still be
routed into the existing durable docs under `docs/human` and `docs/agent` when
they become repository policy.
