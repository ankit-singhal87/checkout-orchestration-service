# Architecture Documentation

This project uses the C4 model for architecture documentation.

## C4 Documents

- [system-context.md](system-context.md): people, external systems, cloud dependencies, and high-level trust boundaries.
- [containers.md](containers.md): deployable/runtime containers in local and deploy modes.
- [components-checkout.md](components-checkout.md): checkout orchestration, tenant resolution, cart, order, and cross-cutting adapters.
- [code-diagrams.md](code-diagrams.md): selected code-level views for high-value areas only.

## Principles

- Build the checkout app as a Laravel modular monolith with clean boundaries. Dependencies point inward: HTTP and infrastructure adapters call application services; application services coordinate domain behavior; domain code does not depend on Laravel, Eloquent, Redis, OpenSearch, or HTTP responses.
- Prefer diagrams that clarify boundaries and data flow.
- Avoid diagramming every class.
- Keep local/dev mode and deploy mode visibly separate.
- Document original architecture decisions, not vendor-specific copies.
