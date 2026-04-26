# C4: Code Diagrams

Phase 1 status: code-level diagrams are intentionally limited to high-value flows. Add or expand diagrams only when tests and component docs no longer make the behavior clear enough.

## Order Confirmation

```mermaid
sequenceDiagram
  participant Browser
  participant Controller as CheckoutConfirmationController
  participant Manager as CheckoutManager
  participant MySQL
  participant Outbox

  Browser->>Controller: POST /checkout/confirm
  Controller->>Manager: confirm(tenant, checkoutId, idempotencyKey)
  Manager->>MySQL: transaction begins
  Manager->>MySQL: find existing order by tenant + idempotency key
  alt existing order
    Manager-->>Controller: existing order
  else no order
    Manager->>MySQL: lock checkout state
    Manager->>MySQL: create order
    Manager->>MySQL: mark checkout confirmed
    Manager->>Outbox: insert order.confirmed event
    Manager-->>Controller: new order
  end
  Controller-->>Browser: redirect to confirmation
```

## Tenant Resolution

```mermaid
sequenceDiagram
  participant Client
  participant Middleware as ResolveTenant
  participant Resolver as TenantResolver
  participant MySQL
  participant Controller

  Client->>Middleware: request with Host header
  Middleware->>Resolver: resolveHost(host)
  Resolver->>MySQL: tenants.host lookup
  alt tenant found
    Middleware->>Controller: attach TenantContext
  else tenant missing
    Middleware-->>Client: 404
  end
```

## API Problem Details

```mermaid
flowchart LR
  ApiController[API controller] --> Validation[Laravel validation]
  Validation --> Problem[Problem Details renderer]
  ApiController --> DomainResult[Application result]
  DomainResult --> ProblemResponse[ProblemDetailsResponse]
  Problem --> Json[application/problem+json]
  ProblemResponse --> Json
```
