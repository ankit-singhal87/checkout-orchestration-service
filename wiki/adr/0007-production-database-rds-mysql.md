# ADR 0007: Production Database Uses RDS MySQL

## Status

Accepted

## Decision

Production deploy mode uses Amazon RDS for MySQL as the durable checkout database. Amazon EKS remains the production application workload platform target, but this project will not run self-managed MySQL inside EKS as the production database.

Local Docker Compose MySQL, and any future `kind` MySQL binding, exist only for local development, testing, and manifest-parity validation.

## Rationale

RDS reduces production operational risk by providing managed backups, patching, Multi-AZ and failover options, monitoring integration, and established operational workflows for stateful database recovery. Keeping production MySQL outside EKS also avoids making this project responsible for Kubernetes stateful database operations, storage failure handling, backup orchestration, and database upgrade procedures.

## Consequences

- Checkout/order writes still use MySQL as the ACID source of truth.
- EKS runs application workloads, workers, ingress, and telemetry components, not the production MySQL server.
- Kubernetes manifests may define local-only MySQL dependencies for `kind`, but EKS overlays must bind the application to RDS.
- AWS deployment remains optional and unapproved until explicit manual approval, budget and cost alerts, ownership/TTL tags, destroy runbooks, and rollback checkpoints exist.
