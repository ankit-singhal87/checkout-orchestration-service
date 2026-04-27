# Agent Docs

Low-token operating entrypoint for AI coding agents.

## Read Order

1. [context.md](context.md)
2. [constraints.md](constraints.md)
3. [repo-map.md](repo-map.md)
4. [commands.md](commands.md)
5. [workflow.md](workflow.md)
6. [codex-workflows.md](codex-workflows.md)
7. [validation.md](validation.md)
8. [guardrails.md](guardrails.md)
9. [task-template.md](task-template.md)

## Durable References

- [../contracts](../contracts) - tenant, checkout state, Problem Details, events, seed data, latency, and BDD/TDD contracts.
- [../standards/php-8.5.md](../standards/php-8.5.md) - PHP implementation standards.
- [../api/openapi.checkout.yaml](../api/openapi.checkout.yaml) - public checkout API contract.
- [agents.md](agents.md) - named project-agent roles, ownership, and handoff boundaries.
- [agents/README.md](agents/README.md) - named-agent task shape and production-adjacent requirements.
- [codex-workflows.md](codex-workflows.md) - mapping from upstream recipe skills to this repo's authority model.
- [context-handoff.md](context-handoff.md) - compact handoff buffer for context defragmentation.
- [../../wiki](../../wiki/README.md) - human background, status, runbooks, architecture, ADRs, and roadmap; read only when needed.

Long-form project knowledge lives in [../../wiki](../../wiki/README.md), not in
the compact agent operating set.
