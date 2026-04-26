# Context Handoff

This file is the compact handoff buffer for orchestrator-owned agent context defragmentation. Keep it short enough that a fresh agent can read it before starting work.

## Current Snapshot

- Phase 2 has local checkout orchestration, Redis Streams outbox publishing, demo runbook commands, default Nginx/PHP-FPM local runtime, optional RoadRunner/Octane performance profile, and a separate Caddy HTTPS/2 parity path.
- Agent MR creation should use `make create-auto-merge-mr`, which creates or reuses a GitLab MR with squash, source-branch deletion, and auto-merge verification.

## Active Threads

- Local Kubernetes direction is docs-only/local-only: Docker Compose remains the fastest app demo runtime, `kind` is the default local EKS-parity manifest validation path, and Amazon EKS is the intended production Kubernetes target but remains unapproved for real deployment until approval, budget/cost alerts, TTL/resource ownership tags, destroy runbooks, and rollback checkpoints exist.
- Default local/TDD runtime is Nginx/PHP-FPM over HTTP/1.1 for speed and familiar debugging. `make up-parity` adds Caddy for HTTPS/2, forwarded headers, security headers, and request-size limits; `make up-roadrunner` is optional performance/runtime testing.

## Defragmentation Rules

- Codex live context is removed by orchestration, not selective deletion: persist selected facts, close or abandon the old worker thread, and start a fresh worker from this file.
- After each MR is created, add at most 1-2 bullets here only for learnings that affect future work.
- Use `HANDOFF_LINES="- One durable line" make defragment-context` to replace volatile active context with selected persisted lines.
- Treat `make defragment-context` as phase 1 only; the orchestrator must then close or abandon the current context-heavy agent and start a fresh one from `docs/agent/README.md` plus this file.
- Use `make show-context` before starting the fresh session so the new agent reads only the compact handoff, not stale chat history.
- After an MR merges, collapse stale branch-specific detail into the relevant durable doc, then start the next agent session from `docs/agent/README.md` and this file.
- Do not paste command logs, full MR descriptions, or long reasoning chains here. Link to the durable doc or MR when the detail must be preserved.
- Delete or rewrite stale bullets instead of appending forever.
