# Tenant Model

The MVP is a shared-database multi-tenant checkout demo. Tenant identity is resolved from trusted context, not from an untrusted public path segment.

## Tenant Resolution

Preferred order:

1. Verified shop host, for example `fashion-demo.localhost`.
2. Signed checkout token claims.
3. Authenticated internal client configuration for internal APIs.

Do not authorize tenant access from a plain `X-Tenant-Id` header or public `/tenants/{tenant}` URL segment.

## Host Mapping

Local demo host mappings:

- `fashion-demo.localhost` -> tenant `fashion-store`, shop `fashion-main`
- `sports-demo.localhost` -> tenant `sports-outlet`, shop `sports-main`

Production-style host mapping should be stored in a tenant-owned table with verified domains. Host lookup must fail closed when a host is unknown or disabled.

## Checkout Token Claims

Signed checkout tokens should use original project claims:

- `tenant_id`
- `shop_id`
- `cart_id`
- `state_id`, when resuming an existing state
- `aud`
- `iss`
- `iat`
- `nbf`
- `exp`

The API may read tenant and shop from a valid signed token, but it must verify those claims match the resolved host or authenticated internal client context.

## API Boundaries

- Storefront and checkout APIs use host/session/token tenant resolution.
- Internal APIs may use `/internal/tenants/{tenantId}` only with service authentication.
- Public APIs must not trust a plain tenant path parameter or client-supplied tenant header.

## Initial Tenants

- `fashion-store`: apparel catalog with sizes, colors, returns messaging, and delivery promise badges.
- `sports-outlet`: sports equipment/apparel catalog with size, fit, bundle, and stock-confidence messaging.

## Data Rules

- Every business table includes `tenant_id`.
- Unique constraints are tenant-scoped.
- Redis keys include a tenant prefix.
- Search indexes or aliases require tenant filtering.
- Logs, metrics, and traces include tenant tags only when safe.

## Product Read Path

- MySQL remains the product and inventory source of truth.
- OpenSearch is a product read model maintained from MySQL through eventual consistency. Checkout correctness must not depend on OpenSearch freshness.
- Product images are served through CloudFront or a local equivalent in dev mode.
- Browsers use a constrained search API or proxy that enforces tenant, field, and query limits. Browsers must not receive raw OpenSearch credentials.

## Rate Limiting

- MVP rate limiting is tenant-based first because tenant identity is available before customer identity in guest checkout flows.
- The design must remain extensible to per-user, per-session, route, and IP limits.
- Token bucket and sliding window log algorithms are acceptable design concepts for future implementation, but this contract does not require a full rate-limiting implementation now.
