# Phase 0 Risk Register

This document captures likely failure modes before implementation starts.

## Risks And Mitigations

### Scope Creep

Risk: too many services, protocols, observability tools, auth options, and cloud assets can bury the checkout demo.

Mitigation: Phase 1 must produce a working Laravel + Blade checkout UI for two tenants before service extraction.

### Cost Creep

Risk: EKS, RDS, OpenSearch, NAT gateways, load balancers, and managed observability can create bills quickly.

Mitigation: local/dev mode is the default. Deploy mode is optional and requires budget alerts, TTL tags, manual approval, and destroy runbooks.

### Tenant Isolation

Risk: shared MySQL schemas can allow cross-tenant reads/writes if queries are not scoped correctly.

Mitigation: every business table includes `tenant_id`, repositories enforce tenant context, and integration tests verify isolation.

### Checkout Consistency

Risk: duplicate orders, wrong totals, stale stock, or overselling inventory.

Mitigation: checkout/order write path uses ACID transactions, idempotency keys, and deterministic state transitions. Async side effects never decide whether an order exists.

### Premature Go Extraction

Risk: splitting services too early adds network, contracts, deployment, and debugging overhead.

Mitigation: Laravel happy path first. Go is added only for selected processors/services with clear concurrency, async, or latency value.

### Observability Sprawl

Risk: adding Grafana Cloud, local Grafana, Datadog, Prometheus, Loki, Tempo, and Jaeger all at once can distract from the MVP.

Mitigation: use OpenTelemetry/OTLP as the contract and keep the observability backend deferred. Local dev runs without a heavy observability stack by default; Grafana Cloud, Datadog, Dash0, or a self-hosted Grafana stack can be added later through an explicit profile.

### CI And Mirror Drift

Risk: GitHub becomes a second workflow instead of a mirror.

Mitigation: GitLab is primary. GitHub is updated by GitLab mirroring and may run lightweight validation only.

### Vendor API/IP Risk

Risk: copying a vendor API, schemas, UI, or proprietary behavior too closely.

Mitigation: create an original educational demo. Keep only broad concepts: checkout state, basket in state, dependent recalculation, and separation of storefront/backend capabilities.

### AWS Deployment Risk

Risk: Terraform can create expensive or hard-to-destroy infrastructure.

Mitigation: no `terraform apply` until deploy mode includes budget alarms, TTL tags, manual approvals, and destroy documentation.
