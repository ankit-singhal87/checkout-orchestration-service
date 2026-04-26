# Phase 2 System Completion Baseline

Phase 2 is closed. It optimized for interview-demo system completeness, not checkout feature breadth. New peripheral services, workers, and async processing belong to [phase-3-peripheral-services.md](phase-3-peripheral-services.md). Remaining observability, service extraction, and deploy breadth belongs to Phase 4+ unless the active Phase 3 plan pulls a narrow slice forward.

## Priority Order

1. **Async boundary:** publish existing `outbox_events` to Redis Streams with an at-least-once Laravel command before considering a Go worker. **Current status:** implemented as `checkout:outbox:publish`; demo helpers are `make demo-outbox-publish` and `make demo-redis-events`.
2. **Runtime story:** keep the default local path fast and familiar with Nginx/PHP-FPM over HTTP, and keep production-boundary behavior in explicit parity/performance profiles. **Current status:** `make up-app` starts Nginx/PHP-FPM over HTTP/1.1; `make up-roadrunner` starts the optional RoadRunner/Octane performance profile; `make up-parity` adds Caddy for HTTPS, HTTP/1.1, HTTP/2, HTTP/3 over QUIC/UDP 443, forwarded headers, security headers, and request-size limits.
3. **Observability path:** make request logs, trace/request IDs, and local collector docs easy to demo before adding provider-specific exporters.
4. **Demo runbook:** document a short path that starts local services, walks two tenants through checkout, shows an outbox event, and verifies tests. **Current status:** [demo-runbook.md](../demo-runbook.md).
5. **Cloud/deploy shape:** keep Docker Compose as the fastest/default app demo runtime and use `kind` as the default local Kubernetes validation target for EKS-parity manifest work. Amazon EKS is the intended production Kubernetes target, but AWS/EKS/Terraform deployment remains unapproved until budget guardrails, ownership/TTL tags, rollback checkpoints, and destroy workflows are explicit.

## Deferred to Phase 4+

- More Laravel checkout endpoints such as vouchers, loyalty, collection points, and address book.
- Go service extraction before a clear async, concurrency, or latency boundary is active.
- Provider-specific observability dashboards before Phase 3 event/worker evidence is demonstrable.
- EKS cluster creation, Terraform apply, registry push, cloud `kubectl` context, managed service setup, or deploy workflow before the local `kind` manifest path and explicit approval, budget/cost alerts, TTL/resource ownership tags, destroy runbooks, and rollback checkpoints exist.
- AWS-only Kubernetes features in base manifests before a separate EKS overlay exists; keep the local base portable and document local-vs-EKS differences.
- Full SCAYLE-shaped API coverage.

## Completed Async Slice

The outbox publisher is implemented as a Laravel console command:

- Read unpublished `outbox_events` in ID order.
- Publish to Redis Stream `checkout:events`.
- Include `event_id`, `event_type`, `aggregate_type`, `aggregate_id`, `tenant_record_id`, `payload`, and `created_at`.
- Mark `published_at` only after a successful publish.
- Keep checkout confirmation independent of Redis availability.

This slice demonstrates transactional consistency, async boundaries, local infrastructure, and retry-friendly event design with less effort than adding more customer-facing checkout features.

## Completed Runtime Slice

The default local runtime is Nginx/PHP-FPM over HTTP/1.1 for fast feedback and familiar debugging. RoadRunner/Octane is an optional performance profile because it changes runtime semantics through long-running workers. Local-production parity uses a Docker Compose override with Caddy for HTTPS, HTTP/1.1, HTTP/2, HTTP/3 over QUIC/UDP 443, and edge-like headers. Do not force HTTPS or a reverse proxy into every TDD loop; gRPC endpoints are the exception and must use HTTP/2 even in the fast path.
