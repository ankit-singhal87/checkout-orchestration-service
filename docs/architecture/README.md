# Architecture Documentation

This project uses the C4 model for architecture documentation.

## C4 Documents

- `system-context.md`: people, external systems, cloud dependencies, and high-level trust boundaries.
- `containers.md`: deployable/runtime containers in local and deploy modes.
- `components-checkout.md`: checkout orchestration, tenant resolution, cart, order, and cross-cutting adapters.
- `code-diagrams.md`: selected code-level views for high-value areas only.

## Principles

- Prefer diagrams that clarify boundaries and data flow.
- Avoid diagramming every class.
- Keep local/dev mode and deploy mode visibly separate.
- Document original architecture decisions, not a SCAYLE clone.
