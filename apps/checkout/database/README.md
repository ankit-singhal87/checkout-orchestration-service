# Database

Laravel migrations, seeders, and factories will live here once the app skeleton exists.

Rules:

- Business tables include `tenant_id`.
- Unique constraints are tenant-scoped.
- Checkout/order writes are ACID.
- Outbox rows are committed in the same transaction as business changes.
