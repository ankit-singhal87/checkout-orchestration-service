Feature: Shipping and payment selection
  Checkout state recalculates when shipping or payment selections change.

  Scenario: Shopper selects a shipping option
    Given a shopper has an addressed checkout state
    When the shopper selects an available shipping option
    Then the checkout state records the selected shipping option
    And totals are recalculated
    And payment selection is still required

  Scenario: Shopper selects a simulated payment method
    Given a shopper selected a shipping option
    When the shopper selects a simulated payment method
    Then the checkout state records the selected payment method
    And order confirmation becomes an allowed next action

  Scenario: Shopper changes payment method
    Given a shopper selected a simulated payment method
    When the shopper selects a different simulated payment method
    Then payment-specific details are recalculated
    And order confirmation remains idempotency-protected
