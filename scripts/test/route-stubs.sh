#!/usr/bin/env sh
set -eu

required_routes="
api.checkout.config.show
api.checkout.state.put
api.checkout.state.show
api.checkout.address.put
api.checkout.shipping-option.put
api.checkout.payment-method.put
api.checkout.order-confirmation.store
shop.index
shop.product.show
cart.items.store
cart.show
checkout.show
checkout.address.update
checkout.shipping-option.update
checkout.payment-method.update
checkout.confirm
checkout.confirmation.show
"

for route_name in $required_routes; do
  if ! grep -R "name('$route_name')" apps/checkout/routes/*.stub >/dev/null 2>&1; then
    echo "Missing route stub name: $route_name" >&2
    exit 1
  fi
done

echo "Route stubs validation passed."
