# Checkout Routes

These route surfaces guide the first Laravel skeleton. They are intentionally smaller than the full API plan.

## Blade Routes

| Method | Path | Name | Purpose |
| --- | --- | --- | --- |
| GET | `/` | `home` | Redirect to default demo shop. |
| GET | `/shop` | `shop.index` | Tenant-aware product listing. |
| GET | `/product/{slug}` | `shop.product.show` | Product detail with variation selection. |
| POST | `/cart/items` | `cart.items.store` | Add selected variation to cart. |
| GET | `/cart` | `cart.show` | Trust-building cart summary. |
| GET | `/checkout` | `checkout.show` | Current checkout state screen. |
| POST | `/checkout/address` | `checkout.address.update` | Submit address. |
| POST | `/checkout/shipping-option` | `checkout.shipping-option.update` | Select shipping option. |
| POST | `/checkout/payment-method` | `checkout.payment-method.update` | Select payment method. |
| POST | `/checkout/confirm` | `checkout.confirm` | Confirm order. |
| GET | `/checkout/confirmation/{orderRef}` | `checkout.confirmation.show` | Confirmation screen. |

## API Routes

| Method | Path | Name | Purpose |
| --- | --- | --- | --- |
| GET | `/api/checkout/config` | `api.checkout.config.show` | Public checkout configuration. |
| PUT | `/api/checkout/state` | `api.checkout.state.put` | Create or resume checkout state. |
| GET | `/api/checkout/state` | `api.checkout.state.show` | Read current checkout state. |
| PUT | `/api/checkout/state/address` | `api.checkout.address.put` | Update checkout address. |
| PUT | `/api/checkout/state/basket/items/{variantId}` | `api.checkout.basket-item.put` | Update or remove a checkout basket item. |
| PUT | `/api/checkout/state/shipping-option` | `api.checkout.shipping-option.put` | Select shipping option. |
| PUT | `/api/checkout/state/payment-method` | `api.checkout.payment-method.put` | Select payment method. |
| POST | `/api/checkout/state/order-confirmation` | `api.checkout.order-confirmation.store` | Confirm order. |

## Route Rules

- Public checkout mutation routes require signed checkout state context or session context.
- Route handlers delegate to application services.
- API errors return RFC 9457 Problem Details.
- Blade routes build view models before rendering.
