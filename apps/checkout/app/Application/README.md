# Application Layer

Application code coordinates use cases and transaction boundaries.

Examples:

- Create or resume checkout state.
- Update checkout address.
- Select shipping option.
- Select payment method.
- Confirm order.
- Publish outbox events after committed writes.

Application services may use repositories, domain services, clocks, ID generators, and adapters through interfaces.
