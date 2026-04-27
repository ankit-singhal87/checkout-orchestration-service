# Checkout State Contract

Checkout state is the central aggregate/read model for the customer-facing checkout flow. It lets guest shoppers resume checkout before an order exists.

## State Machine

```mermaid
stateDiagram-v2
  [*] --> Created
  Created --> Addressed: add address
  Addressed --> ShippingSelected: select shipping
  ShippingSelected --> PaymentSelected: select payment
  PaymentSelected --> Confirming: place order
  Confirming --> Confirmed: order confirmed
  Confirming --> PaymentActionRequired: payment action needed
  Confirming --> Failed: validation or payment failure
  PaymentActionRequired --> Confirmed: payment completed
  Failed --> Addressed: update state
  Confirmed --> [*]
```

Status slugs used by APIs:

- `created`
- `addressed`
- `shipping_selected`
- `payment_selected`
- `confirming`
- `payment_action_required`
- `confirmed`
- `failed`

## Required Concepts

- Tenant and shop context.
- Basket snapshot.
- Customer email and optional identity link.
- Billing and shipping addresses.
- Available and selected shipping options.
- Available and selected payment methods.
- Totals, discounts, vouchers, and tax estimates.
- Validation errors and next allowed actions.
- Idempotency key for state-changing operations.

## Consistency Rules

- Basket, address, shipping, payment, voucher, and confirmation mutations recalculate dependent state.
- Laravel owns the FE/BFF checkout flow, cart state, data collection, tenant validation, and the fast order placement write.
- Order placement accepts the shopper's final checkout intent, persists an idempotent placement record, and emits `order.placed`.
- Order confirmation is a separate service boundary: the Go order preprocessor consumes `order.placed`, materializes the inventory reservation through the Go inventory service, saves the durable order outcome, and emits `order.confirmed`.
- Customer-facing order confirmation must only be shown after the durable MySQL order record exists.
- Async side effects happen through the outbox after the relevant placement or confirmation write commits.
- A duplicate placement or confirmation request with the same tenant, checkout/order, and idempotency key must resolve to the same committed result or the same deterministic failure.
- Payment simulator boundaries exclude real payment credentials, redirects to external providers, webhooks, captures against real processors, and PCI-scoped data.
- Inventory service boundaries exclude external warehouse calls in local mode and non-deterministic stock mutation. Reservation outcomes must be derived from tenant-scoped seeded stock, SKU, quantity, and idempotency key.

## Current Scaffold Baseline

The existing Laravel order-confirmation worker and local simulators are prior implementation scaffolding, not the target architecture. They may remain useful for local replay, outbox, and poison-message tests while the Go inventory service and Go order preprocessor boundary is introduced.

Baseline simulator behavior:

- Payment authorization runs first; invoice succeeds, while card fails when the tenant/shop/checkout/idempotency seed contains `simulate-payment-decline`.
- Inventory reservation runs second and decrements tenant-owned fixture stock only when every cart line has enough visible stock.
- Simulator business failures leave the checkout in `failed`, do not create an order, and do not emit `order.confirmed`.
- Failed checkout states must be repaired through a later checkout mutation before confirmation can be attempted again.

## Phase 3 Event Triggers

- Entering `Confirming` records `checkout.order.confirmation_requested`.
- Committing the placement intent records `order.placed`.
- Go inventory owns tenant-scoped reservation, materialization, release, and failure recovery semantics.
- Go order preprocessor consumes `order.placed` and records `order.confirmed` only after inventory materialization and durable order outcome persistence.
- Extracted payment processors consume capture request events only after the order commit or after an explicit deterministic simulator event.
- Notification, audit, customer, shipment, and projection workers consume committed events after confirmation and must be replay-safe.
