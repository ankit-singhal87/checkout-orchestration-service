Feature: Concurrent checkout execution
  Checkout writes remain correct when requests race.

  Background:
    Given each concurrent test uses a unique run namespace
    And leftover keys from failed tests are not reused

  Scenario: Duplicate confirmation requests create one order
    Given a shopper has a payment-selected checkout state
    When two order confirmation requests use the same idempotency key concurrently
    Then exactly one order is created
    And both requests resolve to the same order result
    And the test cleans up its idempotency keys and checkout data

  Scenario: Competing confirmations cannot oversell stock
    Given two shoppers are checking out the last unit of a tenant variant
    When both shoppers confirm their orders concurrently
    Then only one order reserves the unit
    And the other checkout receives a recoverable inventory Problem Details response
    And the test cleans up its inventory locks and checkout data

  Scenario: Concurrent state updates preserve a valid transition
    Given a shopper has an addressed checkout state
    When shipping selection and payment selection requests race
    Then the checkout state remains in a valid state-machine status
    And the shopper can retry the rejected transition safely
    And the test cleans up its state locks and checkout data
