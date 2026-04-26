# Kubernetes

Kubernetes manifests or Helm-style overlays will live here for local Kubernetes first and Amazon EKS production targeting later. EKS is the intended production Kubernetes platform for application workloads, but production MySQL will run on Amazon RDS for MySQL rather than inside EKS. This directory is docs-only and local-only for now; no production manifests, deploy automation, or approved AWS deployment workflow is implemented here yet.

Docker Compose remains the fastest default local app demo runtime. Use Kubernetes only when intentionally validating future Kubernetes manifests, container orchestration assumptions, or EKS-parity behavior in a local/free loop.

## Default Local Target

`kind` is the default local Kubernetes target for Phase 2 platform validation.

Use `kind` first because it is:

- The closest practical free/local validation loop for Kubernetes API behavior before an EKS overlay exists.
- Close enough to EKS-style container networking, service discovery, image loading, pod scheduling, probes, jobs, config, and resource constraints for early manifest validation.
- Free and fully local, with no AWS account, managed cluster, NAT gateway, or storage spend.
- Common in CI and developer workflows, so future manifests can use standard `kubectl` and Kubernetes APIs.
- Lightweight enough to validate image wiring, Services, ConfigMaps, Secret placeholders, Jobs, probes, small resource requests and limits, and future overlay structure before cloud deployment exists.

`k3d` and Minikube remain acceptable local alternatives for a developer who already has one installed, but they are not the project default. Documentation and future validation commands should assume `kind` unless a later ADR changes the platform direction.

Local Kubernetes should mirror EKS where practical while consciously avoiding AWS-only features in the base manifests. Keep the base portable, separate local `kind` resources from future EKS overlays, avoid managed AWS integrations in the local base, and document known differences when local behavior cannot match EKS.

## Future Manifest Shape

Add manifests only when the Laravel app image and local runtime path are stable enough to validate. Expected milestone order:

1. Local namespace, ConfigMap, Secret placeholder, checkout Deployment, Service, and smoke-test Job.
2. Local dependencies or bindings for MySQL and Redis that match the existing Docker Compose development assumptions. Any MySQL resource in local Kubernetes is for local/dev/test parity only.
3. Local images, local secret placeholders, local storage, and small resource requests and limits.
4. `kubectl port-forward` or local ingress documentation for the demo path.
5. Environment overlays for local `kind` first, then a clearly separated future EKS overlay.
6. Deploy and destroy runbooks with validation, cost notes, ownership tags, TTL tags, and rollback steps before any real AWS deployment.

Future examples must stay local until deploy mode is explicitly approved. They should not require registry pushes, cloud `kubectl` contexts, managed services, Terraform apply, or any cloud deploy workflow. Avoid `LoadBalancer`, AWS Load Balancer Controller, IRSA, EBS/EFS CSI, External Secrets, Route 53, ACM, RDS, ElastiCache, OpenSearch, SQS/SNS, or other managed AWS integrations in the base path; reserve those shapes for a clearly marked future EKS overlay. Future EKS overlays should bind application workloads to RDS MySQL and must not introduce self-managed production MySQL inside EKS.

## Non-Goals

- No AWS/EKS/Terraform readiness is implied by this directory.
- No registry push, cloud cluster access, managed database/cache setup, or deploy workflow is implied.
- No self-managed MySQL-on-EKS production database is implied; local MySQL manifests are development/test fixtures only.
- No GitHub Actions deploy path is allowed; GitHub remains mirror validation only.

## EKS Rule

Amazon EKS is the intended production Kubernetes target. That target does not make AWS deployment ready or approved. Do not add real AWS deployment work, cluster creation, Terraform apply flows, registry pushes, cloud `kubectl` contexts, managed service setup, or CI deploy steps until the project has explicit manual approval, AWS account confirmation, budget limits, cost alerts, TTL tags, cost notes, destroy workflows, resource ownership tags, and rollback checkpoints documented.

GitHub Actions must not deploy. GitLab CI deploy work also remains opt-in and must not create paid cloud resources without explicit approval.
