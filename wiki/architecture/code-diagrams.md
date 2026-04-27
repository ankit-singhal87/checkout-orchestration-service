# C4 Level 4: Code Diagrams

Code-level diagrams are intentionally limited to high-value flows. Add or expand diagrams only when tests and component docs no longer make the behavior clear enough.

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

Key code:

- `app/Http/Controllers/Web/CheckoutConfirmationController.php`
- `app/Http/Controllers/Api/CheckoutOrderConfirmationController.php`
- `app/Application/Checkout/CheckoutManager.php`
- `app/Infrastructure/Persistence/Eloquent/OrderRecord.php`
- `app/Infrastructure/Persistence/Eloquent/OutboxEventRecord.php`

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

Key code:

- `app/Http/Middleware/ResolveTenant.php`
- `app/Application/Tenant/TenantResolver.php`
- `app/Domain/Tenant/TenantContext.php`
- `app/Infrastructure/Persistence/Eloquent/TenantRecord.php`

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

Key code:

- `bootstrap/app.php`
- `app/Http/Responses/ProblemDetailsResponse.php`
- `app/Http/Responses/WebProblemDetailsResponse.php`

## HTTP Correlation

```mermaid
sequenceDiagram
  participant Client
  participant Middleware as ObserveHttpRequest
  participant Controller
  participant Logs as JSON logs

  Client->>Middleware: request with optional X-Request-Id, X-Trace-Id, or traceparent
  Middleware->>Middleware: choose request_id and trace_id
  Middleware->>Controller: attach headers and request attributes
  Controller-->>Middleware: response
  Middleware-->>Client: response with X-Request-Id and X-Trace-Id
  Middleware->>Logs: http_request_completed with route, status, latency, tenant, request_id, trace_id
```

Key code:

- `app/Http/Middleware/ObserveHttpRequest.php`
- `bootstrap/app.php`
- `config/logging.php`

## Outbox Publication

```mermaid
sequenceDiagram
  participant Operator as Demo/operator
  participant Command as PublishOutboxEvents
  participant MySQL
  participant Redis as Redis Stream

  Operator->>Command: php artisan checkout:outbox:publish
  Command->>MySQL: read unpublished outbox_events in ID order
  loop for each event
    Command->>Redis: XADD checkout:events event payload
    Redis-->>Command: stream entry ID
    Command->>MySQL: set published_at after successful publish
  end
  Command-->>Operator: published count
```

Key code:

- `app/Console/Commands/PublishOutboxEvents.php`
- `app/Infrastructure/Persistence/Eloquent/OutboxEventRecord.php`
- `Makefile` targets `demo-outbox-publish` and `demo-redis-events`

## Planned Rate Limiting

```mermaid
flowchart LR
  Request[HTTP request] --> Middleware[Rate limit middleware]
  Middleware --> Key[tenant + route + customer/session/IP key]
  Key --> Adapter[Redis-backed rate limit adapter]
  Adapter --> Redis[(Redis)]
  Adapter --> Allowed{Allowed?}
  Allowed -->|yes| Controller[Controller]
  Allowed -->|no| Problem[429 Problem Details\n/problems/rate-limit-exceeded]
```

Rate limiting is intentionally shown as planned code, not current runtime behavior. It belongs in `app/Http/Middleware` with a Redis-backed infrastructure adapter under `app/Infrastructure`, and it should return RFC 9457 Problem Details rather than controller-specific responses.
