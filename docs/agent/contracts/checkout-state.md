# Checkout State Contract

Checkout state is the central aggregate/read model for the customer-facing checkout flow. It lets guest shoppers resume checkout before an order exists.

## State Machine

```mermaid
stateDiagram-v2
  [*] --> Created
  Created --> Addressed: add address
  Addressed --> ShippingSelected: select shipping
  ShippingSelected --> PaymentSelected: select payment
  PaymentSelected --> Confirming: confirm order
  Confirming --> Confirmed: order committed
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
- Order confirmation must commit a MySQL order record before showing confirmation.
- Async side effects happen through the outbox after the order is committed.
- Inventory reservation and payment simulation are deterministic Phase 3 side effects. They may change internal processor/projection state, but they must not decide whether the committed order exists.
- A duplicate confirmation request with the same tenant, checkout, and idempotency key must resolve to the same committed order result or the same deterministic failure.
- Payment simulator boundaries exclude real payment credentials, redirects to external providers, webhooks, captures against real processors, and PCI-scoped data.
- Inventory simulator boundaries exclude external warehouse calls and non-deterministic stock mutation. Reservation outcomes must be derived from tenant-scoped seeded stock, SKU, quantity, and idempotency key.

## Phase 3 Event Triggers

- Entering `Confirming` records `checkout.order.confirmation_requested`.
- Committing an order records `order.confirmed`.
- Inventory reservation processors consume `order.confirmed` or `inventory.reservation.requested` only after the order commit.
- Payment processors consume payment authorization or capture request events only after the order commit or after an explicit deterministic simulator event.
- Notification, audit, and projection workers consume committed events after confirmation and must be replay-safe.
