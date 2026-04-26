# Local Tools

## Host Tools Required For Phase 1

- Git
- Docker
- Docker Compose

These are the only hard host requirements for the default local workflow. Application runtimes and backing services should run in containers unless there is a clear reason to install them locally.

## Recommended Host Tools

- PHP CLI matching the app version, for fast editor integration and one-off local checks.
- Composer, useful for inspecting dependencies or running scripts outside containers.
- Node.js and npm, only if frontend asset compilation is needed outside containers.
- `make` or plain shell scripts for common local commands.
- `curl`, `jq`, and `openssl` for API/debug workflows.
- `mysql` client and `redis-cli`, optional because container exec can also be used.
- GitLab CLI `glab`, optional.

## Debugging Tools

- Cursor or VS Code PHP Debug extension for listening to Xdebug connections.
- Xdebug installed in the PHP/Laravel container, disabled by default and enabled through a Compose profile or environment override.
- RoadRunner CLI inside the Laravel container, not required on the host.
- Delve for Go debugging later, preferably in the Go worker container or a dedicated debug image.

## Container-Managed Services

Run these through Docker Compose with named volumes or bind-mounted config:

- MySQL with `mysql-data` for durable local schemas and seed data.
- Redis with `redis-data` for append-only local cache/stream state.
- OpenSearch by default with `opensearch-data`; use Elasticsearch only if a later ADR changes the search backend.
- OpenTelemetry Collector with bind-mounted collector config.
- Prometheus with `prometheus-data`.
- Loki with `loki-data`.
- Tempo with `tempo-data`.
- Grafana with `grafana-data`.
- Keycloak with `keycloak-data`, optional and not required for guest checkout.

Container-managed services keep the host clean and make reset behavior explicit. To reset local state, stop Compose and remove the named volumes intentionally.

## Later Host Tools

- Go, once Go workers/services are introduced.
- Terraform, only for optional AWS deploy work.
- `kubectl`, once local Kubernetes or EKS assets are active.
- `kind` or `k3d`, for local Kubernetes validation.
- `protoc` and related plugins, once gRPC contracts are generated.

## Optional

- Datadog CLI or agent tooling, only if Datadog becomes an active backend.
- AWS CLI, only after an AWS account and budget guardrails exist.

Run:

```bash
sh scripts/dev/check-tools.sh
```

