# Context Handoff

This file is the compact handoff buffer for orchestrator-owned agent context defragmentation. Keep it short enough that a fresh agent can read it before starting work.

## Current Snapshot

- Phase 2 is closed with local checkout orchestration, Redis Streams outbox publishing, demo runbook commands, default Nginx/PHP-FPM runtime, optional RoadRunner profile, and Caddy HTTPS/H1/H2/H3 edge parity.
- Phase 3 is active for peripheral services and workers: async backbone hardening, worker runtime, inventory/payment simulators, notification/audit processors, and service extraction review only after boundaries prove useful.
- Agent MR creation should use `make create-auto-merge-mr`, which creates or reuses a GitLab MR with squash, source-branch deletion, and auto-merge verification.

## Active Threads

- Phase 4+ holds local Kubernetes/EKS/deploy work unless explicitly pulled forward: Docker Compose remains the fastest app demo runtime, `kind` is the default local EKS-parity manifest validation path, and Amazon EKS is the intended production Kubernetes target but remains unapproved for real deployment until approval, budget/cost alerts, TTL/resource ownership tags, destroy runbooks, and rollback checkpoints exist.
- Default local/TDD runtime is Nginx/PHP-FPM over HTTP/1.1 for speed and familiar debugging. `make up-parity` adds Caddy for HTTPS, HTTP/1.1, HTTP/2, HTTP/3 over QUIC/UDP 443, forwarded headers, security headers, and request-size limits; `make up-roadrunner` is optional performance/runtime testing.

## Defragmentation Rules

- Keep this file limited to durable facts needed by the next session.
- After each MR is created or merged, add at most 1-2 bullets only when they
  affect future work.
- Move lasting decisions into durable docs instead of appending here forever.
- Do not paste command logs, full MR descriptions, or long reasoning chains here. Link to the durable doc or MR when the detail must be preserved.
- Delete or rewrite stale bullets instead of appending forever.
