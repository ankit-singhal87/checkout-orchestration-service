# Local Tools

## Host Tools Required For Phase 1

- Git
- Docker
- Docker Compose

These are the only hard host requirements for the default local workflow. Application runtimes and backing services should run in containers unless there is a clear reason to install them locally.

Docker must be reachable by the current user, not merely installed. [scripts/dev/check-tools.sh](../../scripts/dev/check-tools.sh) verifies daemon access because Laravel bootstrap, app tests, and local services all depend on `/var/run/docker.sock` access.

## Recommended Host Tools

- PHP 8.5 CLI matching the app version, optional for editor integration and one-off local checks.
- Composer, optional because Laravel bootstrapping and tests can run through Docker.
- Node.js and npm, only if frontend asset compilation is needed outside containers.
- `make` for common local commands; plain shell scripts remain the source of truth and fallback.
- `curl`, `jq`, and `openssl` for API/debug workflows.
- `mysql` client and `redis-cli`, optional because container exec can also be used.
- GitLab CLI `glab`, optional.

## Common Commands

Prefer Makefile targets for repeated local and agent workflows. Targets are thin wrappers over scripts, so use the underlying script only when `make` is unavailable or when a script-specific environment override is clearer.

```bash
make help
make check-tools
make install-host-tools
make up
make up-app
make up-roadrunner
make up-parity
make down
make bootstrap-checkout
make test-checkout
make test-checkout-runtime
make test-checkout-parity
make validate
make pre-push
make pre-push-full
make show-context
make defragment-context
make create-auto-merge-mr
```

Use `make pre-push-full` before pushing changes that affect checkout runtime or behavior. It runs the same pre-push checks with checkout app tests enabled.

Use `make create-auto-merge-mr` only after the user has authorized MR creation for the branch. It creates or reuses a GitLab MR, enables squash, requests source-branch deletion, and enables auto-merge once checks pass. Set `MR_TITLE`, `MR_DESCRIPTION`, `SQUASH_MESSAGE`, `SOURCE_BRANCH`, or `TARGET_BRANCH` to override defaults.

Use `make defragment-context` when an agent needs to persist only selected context before the orchestrator starts fresh. Provide newline-separated bullets through `HANDOFF_LINES` or a file path through `HANDOFF_FILE`; the command replaces the volatile `Active Threads` section in `docs/agent/context-handoff.md`. The orchestrator then closes or abandons the context-heavy worker/session and starts a new one from `docs/agent/README.md` plus the compact handoff. Use `make show-context` to inspect the compact handoff.

Use `make install-host-tools` only for local workstation bootstrap or repair. It installs missing essential tools such as `git`, `make`, `curl`, `jq`, `openssl`, Node.js/npm, `glab`, Docker, Docker Compose, and Codex where supported. It does not authenticate external CLIs or manage secrets; run `glab auth login` separately.

## Debugging Tools

- Cursor or VS Code PHP Debug extension for listening to Xdebug connections.
- Xdebug installed in the PHP 8.5 Laravel container, disabled by default and enabled with `XDEBUG_MODE=debug`.
- RoadRunner CLI inside the Laravel container for the optional performance profile; not required on the host.
- Delve for Go debugging later, preferably in the Go worker container or a dedicated debug image.

## Container-Managed Services

Run these through Docker Compose with named volumes or bind-mounted config:

- PHP and Composer for Laravel bootstrap and local test execution.
- MySQL with `mysql-data` for durable local schemas and seed data.
- Redis with `redis-data` for append-only local cache/stream state.
- OpenSearch by default with `opensearch-data`; use Elasticsearch only if a later ADR changes the search backend.
- Optional observability profile selected later. Local checkout development should not require Grafana Cloud, Datadog, Dash0, Prometheus, Loki, Tempo, or Grafana.
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

- Grafana Cloud, Datadog, Dash0, or self-hosted Grafana tooling, only if that backend becomes an active profile.
- AWS CLI, only after an AWS account and budget guardrails exist.

Run:

```bash
make check-tools
```

To install missing essential host tools on a supported workstation:

```bash
make install-host-tools
```
