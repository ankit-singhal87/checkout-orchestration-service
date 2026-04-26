# Context Handoff

This file is the compact handoff buffer for orchestrator-owned agent context defragmentation. Keep it short enough that a fresh agent can read it before starting work.

## Current Snapshot

- Phase 2 has local checkout orchestration, Redis Streams outbox publishing, demo runbook commands, and an opt-in RoadRunner wrapper backed by Laravel Octane/RoadRunner dependencies.
- Agent MR creation should use `make create-auto-merge-mr`, which creates or reuses a GitLab MR with squash, source-branch deletion, and auto-merge verification.

## Active Threads

- Local Kubernetes direction is docs-only/local-only: Docker Compose remains the fastest app demo runtime, `kind` is the default local EKS-parity manifest validation path, and Amazon EKS is the intended production Kubernetes target but remains unapproved for real deployment until approval, budget/cost alerts, TTL/resource ownership tags, destroy runbooks, and rollback checkpoints exist.
- RoadRunner remains opt-in for local development; production-style runtime hardening is separate from the installed Composer dependency slice.

## Defragmentation Rules

- Codex live context is removed by orchestration, not selective deletion: persist selected facts, close or abandon the old worker thread, and start a fresh worker from this file.
- After each MR is created, add at most 1-2 bullets here only for learnings that affect future work.
- Use `HANDOFF_LINES="- One durable line" make defragment-context` to replace volatile active context with selected persisted lines.
- Treat `make defragment-context` as phase 1 only; the orchestrator must then close or abandon the current context-heavy agent and start a fresh one from `docs/agent/README.md` plus this file.
- Use `make show-context` before starting the fresh session so the new agent reads only the compact handoff, not stale chat history.
- After an MR merges, collapse stale branch-specific detail into the relevant durable doc, then start the next agent session from `docs/agent/README.md` and this file.
- Do not paste command logs, full MR descriptions, or long reasoning chains here. Link to the durable doc or MR when the detail must be preserved.
- Delete or rewrite stale bullets instead of appending forever.
