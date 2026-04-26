# ADR 0003: OpenTelemetry First Observability

## Status

Accepted

## Decision

Use OpenTelemetry/OTLP as the application observability contract. Do not make Grafana Cloud, Datadog, Dash0, or a self-hosted Grafana stack a hard dependency in Phase 1.

Local development defaults to application logs on stdout and the smallest runtime needed for checkout behavior. Managed observability backends and local observability stacks are optional profiles that can be selected later without changing application instrumentation.

## Consequences

- Application instrumentation stays vendor-neutral.
- Local development can run without paid SaaS or a heavy local observability stack.
- Grafana Cloud, Datadog, Dash0, or a self-hosted Grafana stack can be connected through a collector/agent/exporter profile without rewriting checkout code.
- Provider-specific credentials, pricing, retention, dashboards, and agents are deferred until the observability slice is intentionally selected.
- The application must avoid provider-specific tags, APIs, and SDK assumptions outside edge adapters.
