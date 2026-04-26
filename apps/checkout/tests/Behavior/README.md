# Behavior Specs

Readable BDD scenarios for checkout behavior live here.

Rules:

- Write scenarios in shopper/business language before implementation.
- Keep scenarios focused on observable behavior, not framework internals.
- Link scenarios to feature/API tests that make them executable.
- Keep at least one `.feature` file for each critical checkout behavior area.

Initial behavior areas:

- Tenant-aware browsing.
- Cart item creation.
- Checkout state creation/resume.
- Address, shipping, and payment updates.
- Idempotent order confirmation.
- Tenant isolation failure cases.
- Checkout configuration.
- Failure recovery for stock and payment scenarios.
- Concurrent execution and race-condition scenarios.
