# Domain Layer

Domain code owns business concepts and invariants.

Initial contexts:

- Tenant
- Catalog
- Cart
- Checkout
- Order
- Inventory
- Pricing
- Identity

Domain code must not depend on HTTP requests, Blade, Redis, OpenSearch, or external transport concerns.
