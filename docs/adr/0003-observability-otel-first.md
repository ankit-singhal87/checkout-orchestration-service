# ADR 0003: OpenTelemetry First Observability

## Status

Accepted

## Decision

Use OpenTelemetry/OTLP as the observability contract. Use Prometheus, Loki, Grafana, and Jaeger or Tempo locally. Prefer Grafana Cloud for low-cost managed observability. Keep Datadog optional.

## Consequences

- Application instrumentation stays vendor-neutral.
- Local development can run without paid SaaS.
- Grafana Cloud can be connected without rewriting instrumentation.
- Datadog remains available as a production-style alternative.