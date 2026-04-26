# Debugging Guide

## Laravel And RoadRunner

- Application logs should be structured JSON and include `trace_id`, `request_id`, `tenant_id`, and route.
- RoadRunner worker reload commands will be added when the Laravel app is created.
- Xdebug belongs in the Laravel PHP container, disabled by default for performance.
- The host only needs an IDE listener such as the Cursor or VS Code PHP Debug extension.
- Use path mappings from the container app path, for example `/app`, to the host workspace path.
- Enable Xdebug through an explicit Compose profile or environment override when stepping through code.

## Go Workers

- Use structured logs with the same field names as Laravel.
- Debugger setup will be added when the first Go worker is introduced.
- Prefer Delve in a Go worker debug container instead of requiring a host Go debugger.

## Data Stores

- MySQL starts as one local instance with multiple logical schemas.
- Redis is used for cache, locks, rate limits, and Redis Streams in local mode.
- OpenSearch is a read model/projection, not a transactional source of truth.
- Keycloak is optional local identity infrastructure and should not be required for guest checkout.
- MySQL, Redis, OpenSearch, Keycloak, Prometheus, Loki, Tempo, and Grafana should persist data through named Docker volumes.

## Traces And Metrics

- Local traces should be visible in Tempo or Jaeger.
- Local metrics should be visible in Prometheus/Grafana.
- Local logs should be visible in Loki/Grafana once log shipping is wired.
- Grafana Cloud integration is optional until credentials are configured outside the repository.

## Resetting Local State

Use `sh scripts/dev/down.sh` to stop services without deleting data. Remove Docker volumes only when an intentional clean reset is needed.