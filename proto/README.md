# Proto Contracts

Shared gRPC contracts live here when a boundary is intentionally extracted from Laravel.

## Phase 3 Scope

- Keep this directory as a contract placeholder.
- Do not generate proto code until a real service boundary is approved.
- Prefer documenting expected request/response concepts in [docs/agent/contracts](../docs/agent/contracts) before adding `.proto` files.
- Use the Phase 3 event contracts and worker READMEs as the first boundary definition for inventory, payment, order processing, audit, and projection behavior.

## Extraction Criteria

Add `.proto` contracts only after:

- The Laravel/local worker contract is implemented and replay-safe.
- The candidate boundary has measured concurrency, async throughput, or latency pressure.
- Tenant scoping, idempotency keys, retry/poison behavior, and simulator determinism are already documented.
- A worker lane is explicitly assigned to create the proto contract and generated code policy.

Until then, event envelopes in [domain-events.md](../docs/agent/contracts/domain-events.md) are the stable integration contract.
