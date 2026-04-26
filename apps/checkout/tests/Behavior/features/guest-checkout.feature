Feature: Guest checkout
  Guest shoppers can complete checkout without creating an account first.

  Background:
    Given the "fashion-store" tenant is active
    And deterministic catalog and checkout seed data exists

  Scenario: Guest shopper confirms an order
    Given a shopper has an in-stock item in the cart
    When the shopper opens checkout
    And enters a valid shipping address
    And selects an available shipping option
    And selects a simulated payment method
    And confirms the order
    Then exactly one order is created
    And the checkout state is confirmed
    And the shopper sees the confirmation page
    And post-order side effects are queued through the outbox

  Scenario: Guest shopper sees validation errors before confirmation
    Given a shopper has an in-stock item in the cart
    When the shopper opens checkout
    And tries to confirm without an address
    Then the checkout state is not confirmed
    And the shopper sees address validation errors
