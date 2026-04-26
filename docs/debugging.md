# Debugging Guide

## Laravel And RoadRunner

- Application logs should be structured JSON and include `trace_id`, `request_id`, `tenant_id`, and route.
- RoadRunner worker reload commands will be added when the Laravel app is created.
- Xdebug is optional and should be disabled by default for performance.

## Go Workers

- Use structured logs with the same field names as Laravel.
- Debugger setup will be added when the first Go worker is introduced.

## Data Stores

- MySQL starts as one local instance with multiple logical schemas.
- Redis is used for cache, locks, rate limits, and Redis Streams in local mode.
- OpenSearch is a read model/projection, not a transactional source of truth.

## Traces And Metrics

- Local traces should be visible in Jaeger or Tempo.
- Local metrics should be visible in Prometheus/Grafana.
- Grafana Cloud integration is optional until credentials are configured outside the repository.