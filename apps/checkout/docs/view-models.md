# Blade View Models

Blade templates receive explicit view models built by application services or presenters. This keeps the UI simple and prevents database access in views.

## Initial View Models

### ProductCardViewModel

- Product ID.
- Slug.
- Name.
- Primary image URL.
- Price display.
- Variation summary.
- Stock message.
- Trust badges.
- Tenant branding hints.

### CartViewModel

- Cart ID.
- Tenant ID.
- Items.
- Subtotal, discounts, shipping estimate, and total.
- Delivery promise.
- Return promise.
- Payment trust labels.
- Next checkout action.

### CheckoutStateViewModel

- Checkout state ID.
- Current step.
- Basket summary.
- Address forms.
- Available shipping options.
- Available payment methods.
- Totals.
- Validation errors.
- Next allowed actions.

### ConfirmationViewModel

- Order reference.
- Tenant branding.
- Confirmed items and totals.
- Delivery estimate.
- Async processing status.
- Optional account creation prompt.

## Rules

- View models are serializable and testable.
- View models expose display-ready values, not Eloquent models.
- Validation errors use stable field names aligned with API Problem Details.
