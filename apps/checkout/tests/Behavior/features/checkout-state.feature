Feature: Checkout state
  Checkout state is resumable and recalculates when dependent inputs change.

  Scenario: Shopper creates checkout state from cart
    Given a shopper has an in-stock item in the cart
    When checkout state is created
    Then the state contains the basket snapshot
    And the state lists the next allowed actions
    And no order exists yet

  Scenario: Address update recalculates shipping and totals
    Given a shopper has created checkout state
    When the shopper adds a valid shipping address
    Then shipping options are recalculated
    And totals are recalculated
    And the next allowed actions are updated

  Scenario: Basket update invalidates dependent selections
    Given a shopper selected shipping and payment
    When the shopper changes basket quantity
    Then dependent totals are recalculated
    And invalid shipping or payment selections are cleared
