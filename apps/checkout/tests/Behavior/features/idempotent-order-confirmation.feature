Feature: Idempotent order confirmation
  Repeated confirmation attempts with the same idempotency key must not create duplicate orders.

  Scenario: Retry returns the original order result
    Given a shopper has a payment-selected checkout state
    And the shopper has an idempotency key
    When the shopper confirms the order
    And the shopper retries confirmation with the same idempotency key
    Then exactly one order is created
    And both responses reference the same order

  Scenario: Conflict is returned for incompatible retry
    Given a shopper has a confirmed checkout state
    When the shopper retries confirmation with the same idempotency key and different request body
    Then no additional order is created
    And the API returns a checkout conflict Problem Details response
