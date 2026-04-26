# Problem Details Contract

Public HTTP APIs use RFC 9457 Problem Details for errors.

## Shape

```json
{
  "type": "https://checkout.example.test/problems/checkout-state-conflict",
  "title": "Checkout state conflict",
  "status": 409,
  "detail": "The checkout state changed. Refresh the checkout and try again.",
  "instance": "/api/checkout/state/order-confirmation",
  "traceId": "01HV...",
  "tenant": "fashion-store",
  "shop": "fashion-main",
  "errors": []
}
```

## Rules

- Use `application/problem+json`.
- Include a trace or request ID.
- Include tenant information only from the allowlist below, using `tenant` and `shop` for public slugs.
- Validation errors use stable field paths in `errors`.
- Internal exceptions must not leak stack traces, SQL, secrets, tokens, or infrastructure details.

## Safe Tenant Fields

Client-visible error responses may include:

- Public tenant slug, for example `fashion-store`.
- Public shop slug, for example `fashion-main`.

Client-visible error responses must not include internal database IDs, secret token claims, raw host mapping records, or authorization policy internals.

## Problem Type Registry

| Type | Status | Use |
| --- | --- | --- |
| `/problems/validation-failed` | 422 | Request fields failed validation. |
| `/problems/checkout-state-conflict` | 409 | State changed or idempotency body mismatch. |
| `/problems/tenant-access-denied` | 403 | Tenant context does not match the resource. |
| `/problems/checkout-state-not-found` | 404 | State handle or token points to no visible state. |
| `/problems/rate-limit-exceeded` | 429 | Tenant, customer, route, or IP rate limit exceeded. |
| `/problems/internal-error` | 500 | Unexpected server error with redacted details. |

Problem `type` values should be absolute URIs in API responses, using the public problem base URL plus the registry path.

## Validation Error Item

```json
{
  "field": "shippingAddress.postalCode",
  "code": "required",
  "message": "Postal code is required."
}
```

Validation `message` is safe user-facing text. Do not pass raw exception messages into `message`.
