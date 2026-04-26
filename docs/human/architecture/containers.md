# C4 Level 2: Containers

## Local/Dev Mode - Current Phase 2

```mermaid
flowchart LR
  Browser[Browser] --> Nginx[Nginx HTTP/1.1]
  ApiClient[API client] --> Nginx
  Nginx --> Fpm[PHP-FPM Laravel app]
  BrowserParity[Browser parity path] --> Proxy[Caddy HTTPS/2 proxy]
  Proxy --> Nginx
  BrowserPerf[Performance path] --> RoadRunner[RoadRunner/Octane]
  Fpm --> MySQL[(MySQL 8.4\nsource of truth)]
  Fpm --> Redis[(Redis\ncache locks streams)]
  RoadRunner --> MySQL
  RoadRunner --> Redis
  Fpm --> Outbox[(outbox_events table)]
  Publisher[checkout:outbox:publish command] --> Outbox
  Publisher --> RedisStream[(Redis Stream\ncheckout:events)]
  Fpm --> Stdout[stdout / Laravel logs]
  Tests[Pest feature tests] --> Fpm
  GitLab[GitLab CI] --> Tests
```

Current local default is intentionally small:

- `mysql`: MySQL source-of-truth database.
- `redis`: Redis support service for cache, locks, idempotency/rate-limit backing, and Redis Streams.
- `checkout`: Laravel app running as PHP-FPM for the default local path, enabled with `COMPOSE_PROFILES=app`.
- `nginx`: default local web entrypoint on HTTP/1.1, published as `http://localhost:8080`.
- Startup runs pending migrations and idempotent seeders before serving the app.
- `checkout-roadrunner`: optional RoadRunner/Octane performance profile, started with `make up-roadrunner`.
- `make up-parity` layers Caddy in front of the default web stack for local-production parity: HTTPS, HTTP/2, forwarded headers, security headers, and request-size limits.
- Keep HTTPS and the reverse proxy out of the default TDD loop. Future gRPC endpoints are the exception and must use HTTP/2 even on the fast path.
- The outbox publisher is a Laravel command, `checkout:outbox:publish`, exposed for demos through `make demo-outbox-publish`.

Optional local profiles:

- `search`: OpenSearch read-model service. It is not wired into checkout/order writes in the current local demo.
- `observability`: OpenTelemetry Collector, Prometheus, Loki, Tempo, Jaeger, and Grafana services kept as an optional fallback. The current app demonstrates request/trace correlation and JSON logs; full app OTLP export remains a later slice.
- `identity`: Keycloak placeholder for later optional auth work.

## Deploy Mode - Planned/Optional

```mermaid
flowchart LR
  Browser[Browser] --> CloudFront[CloudFront + WAF optional edge]
  CloudFront --> Ingress[ALB / Kubernetes ingress]
  Ingress --> Laravel[Laravel RoadRunner checkout app]
  Laravel --> RDS[(RDS MySQL)]
  Laravel --> ElastiCache[(ElastiCache Redis)]
  Laravel -. projection .-> Search[(Amazon OpenSearch Service)]
  Laravel --> Outbox[(outbox_events)]
  Publisher[Outbox publisher] --> ElastiCache
  Publisher -. later phase .-> SqsSns[SQS / SNS]
  Laravel --> Otlp[OTLP telemetry boundary]
  Otlp -. selected later .-> Backend[Grafana Cloud / Datadog / Dash0 / self-hosted]
```

Deploy mode remains optional and manually approved. RoadRunner is the preferred production-style PHP runtime, but Kubernetes, OpenSearch projections, external message brokers, workers, and cloud telemetry exporters stay behind explicit later slices.

Amazon EKS is the production target for application workloads. Production MySQL runs on Amazon RDS for MySQL, not as a self-managed StatefulSet or other in-cluster database on EKS. Local Docker Compose MySQL, and any future `kind` MySQL binding, exist only for development, testing, and local manifest validation.

## Non-Negotiable Boundaries

- MySQL decides whether checkout state and orders exist.
- Production MySQL is Amazon RDS for MySQL; this keeps managed backups, patching, Multi-AZ/failover options, and stateful database operations outside EKS.
- OpenSearch is a rebuildable projection/read model only.
- The transactional outbox table is the durability boundary; Redis Streams publication is local/demo async delivery and remains retryable.
- Go workers/services require a documented concurrency, async, or latency reason plus a stable contract.
- Observability backend selection stays behind the OTLP boundary.
