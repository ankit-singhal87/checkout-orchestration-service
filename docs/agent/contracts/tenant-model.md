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
