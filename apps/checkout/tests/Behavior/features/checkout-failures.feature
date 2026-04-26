Feature: Checkout failure recovery
  Shoppers can recover from validation, stock, and payment failures without duplicate orders.

  Scenario: Out-of-stock item blocks confirmation
    Given a shopper has an out-of-stock item in the cart
    When the shopper confirms the order
    Then no order is created
    And the checkout state is failed
    And the shopper sees an inventory Problem Details response

  Scenario: Payment declined returns recoverable state
    Given a shopper has a payment-selected checkout state
    And the simulated payment method is configured to decline
    When the shopper confirms the order
    Then no confirmed order is shown
    And the checkout state requires a new payment method
    And the shopper can select another simulated payment method
