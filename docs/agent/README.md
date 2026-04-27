# Agent Docs

Low-token operating entrypoint for AI coding agents.

## Read Order

1. [context.md](context.md)
2. [constraints.md](constraints.md)
3. [repo-map.md](repo-map.md)
4. [commands.md](commands.md)
5. [workflow.md](workflow.md)
6. [validation.md](validation.md)
7. [guardrails.md](guardrails.md)
8. [task-template.md](task-template.md)

## Durable References

- [contracts](contracts) - tenant, checkout state, Problem Details, events, seed data, latency, and BDD/TDD contracts.
- [coding-standards/php-8.5.md](coding-standards/php-8.5.md) - PHP implementation standards.
- [api/openapi.checkout.yaml](api/openapi.checkout.yaml) - public checkout API contract.
- [agents.md](agents.md) - named project-agent roles, ownership, and handoff boundaries.
- [agents/README.md](agents/README.md) - named-agent task shape and production-adjacent requirements.
- [local-tools.md](local-tools.md), [debugging.md](debugging.md), [mirroring.md](mirroring.md) - detailed operational guides.
- [planning](planning) - phase state and roadmap details; read only when phase planning is relevant.
- [context-handoff.md](context-handoff.md) - compact handoff buffer for context defragmentation.
- [glossary.md](glossary.md) - short operational definitions.

Human-facing architecture and ADR narrative lives in [../human](../human/README.md).
