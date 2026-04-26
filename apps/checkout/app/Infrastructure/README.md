# Infrastructure Layer

Infrastructure code adapts framework and platform details to application interfaces.

Examples:

- Eloquent repositories.
- Redis cache, lock, rate limit, and idempotency adapters.
- OpenSearch projection writers.
- Outbox storage and publisher adapters.
- OpenTelemetry instrumentation glue.

Infrastructure code must preserve tenant scoping and avoid leaking secrets into logs.
