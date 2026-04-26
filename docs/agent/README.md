# Agent Docs

Concise docs for machine reading and lower-token agent context.

Start here:

- [planning/phase-1-current-state.md](planning/phase-1-current-state.md) - current foundation/closed Phase 2 implementation snapshot and verification commands.
- [planning/phase-3-peripheral-services.md](planning/phase-3-peripheral-services.md) - active Phase 3 plan for peripheral services, workers, async processing, and parallel lanes.
- [planning/phase-2-system-completion.md](planning/phase-2-system-completion.md) - closed Phase 2 system-completion baseline.
- [context-handoff.md](context-handoff.md) - compact current handoff buffer for context defragmentation between MRs and sessions.
- [agents.md](agents.md) - project-agent roles, parallel work strategy, merge playbook, and stop conditions.
- [agents/README.md](agents/README.md) - narrow named-agent definition requirements and template for production-adjacent work.
- [demo-runbook.md](demo-runbook.md) - local Phase 2 demo path for checkout, outbox, Redis Streams, and correlation evidence.
- [contracts](contracts) - tenant, checkout state, Problem Details, events, seed data, latency, and BDD/TDD contracts.
- [api/openapi.checkout.yaml](api/openapi.checkout.yaml) - public checkout API contract.
- [coding-standards/php-8.5.md](coding-standards/php-8.5.md) - PHP implementation standards.
- [local-tools.md](local-tools.md), [debugging.md](debugging.md), and [mirroring.md](mirroring.md) - local execution, debugging, and repository workflow. Prefer `make help`, `make validate`, and `make pre-push-full` for common commands.

Use [../human](../human) for long-form architecture, ADRs, and phase planning context.
