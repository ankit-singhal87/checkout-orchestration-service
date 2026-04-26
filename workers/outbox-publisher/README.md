# Outbox Publisher

Publishes committed domain events from the database outbox to local Redis Streams or deploy-mode messaging.

## Phase 1 Scope

- Define the outbox ownership and transport mapping.
- Keep the implementation choice open: Laravel worker first, Go worker later if throughput or operational needs justify it.

## Transports

- Local/dev: Redis Streams.
- Deploy mode: AWS SQS/SNS, after cloud guardrails exist.
