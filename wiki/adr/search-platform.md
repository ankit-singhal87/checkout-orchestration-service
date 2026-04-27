# ADR: Search Platform Direction

## Status

Draft placeholder

## Context

The MVP uses local OpenSearch as a free read-model option. Deploy-mode search remains optional and should be selected only after the Laravel checkout path is stable.

## Direction

Default to AWS OpenSearch for the first AWS/EKS deployment path because it keeps IAM, networking, Terraform ownership, and cost controls inside the AWS account boundary.

Document Elastic Cloud as a later alternative when Elastic-native features, Kibana parity, or managed relevance tooling matter more than AWS-native integration.
