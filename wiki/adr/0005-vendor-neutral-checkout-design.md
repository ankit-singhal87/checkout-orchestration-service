# ADR 0005: Vendor-Neutral Checkout Design

## Status

Accepted

## Decision

Use public headless-commerce concepts as inspiration for checkout flows, not vendor documentation as a source to copy API specs, schemas, tenant conventions, or UI behavior.

## Consequences

- The demo can discuss generic headless-commerce architecture while remaining original.
- The OpenAPI spec will be simplified and project-specific.
- Endpoint naming may borrow broad concepts like checkout state, but not any vendor's exact API surface.
- Proprietary behavior and UI details are out of scope.
