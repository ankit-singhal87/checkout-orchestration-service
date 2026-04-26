# Terraform

Optional deploy-mode infrastructure will live here after AWS budget guardrails exist. The production database target is Amazon RDS for MySQL; EKS is for application workloads, not self-managed production MySQL.

## Phase 1 Scope

- Keep this as a placeholder.
- Do not run `terraform apply`.
- Future modules should support `terraform plan` without requiring a live deployment.

## Required Before Cloud Apply

- Budget alarms.
- Cost alerts.
- TTL tags.
- Resource ownership tags.
- Manual approval.
- Destroy runbook.
- Rollback checkpoints.
- Separate deployment credentials.

Future Terraform must model RDS MySQL as the production database service so managed backups, patching, Multi-AZ/failover options, and restore workflows stay outside Kubernetes. Local Docker Compose MySQL and future local `kind` MySQL bindings remain dev/test-only substitutes.
