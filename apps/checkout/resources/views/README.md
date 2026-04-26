# Blade Views

Blade views render explicit view models.

Rules:

- No database queries.
- No domain decisions.
- No direct Redis, OpenSearch, or external service access.
- Keep templates small and tenant-aware through provided branding data.
