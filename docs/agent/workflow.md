# Agent Workflow

1. Read [docs/agent/README.md](README.md).
2. Inspect only the files relevant to the task.
3. Check current worktree status; do not revert edits made by others.
4. Make focused changes inside the assigned ownership scope.
5. Run the cheapest relevant validation from [validation.md](validation.md).
6. Update agent docs if operational facts changed.
7. Update human docs only if reviewer-facing behavior or architecture changed.
8. Summarize diff, validation, risks, and any intentionally unchanged mixed docs.

## Routing Rules

- Use [agents.md](agents.md) to choose the narrowest named project-agent lane.
- Use [codex-workflows.md](codex-workflows.md) when a task benefits from an
  upstream recipe skill.
- Use [validation.md](validation.md) to pick the cheapest relevant checks.
- Use [context-handoff.md](context-handoff.md) only for compact cross-session
  state; move lasting decisions into durable docs.

Do not edit upstream-managed `.agents/skills` or `.codex/agents` files during
ordinary repository work. Treat them as vendored workflow assets managed through
the root npm package.
