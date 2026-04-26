Feature: Checkout resume
  Guest shoppers can resume an existing checkout state without creating a duplicate order.

  Scenario: Shopper resumes checkout state from session
    Given a shopper has created checkout state
    When the shopper returns to checkout with the same session
    Then the existing checkout state is loaded
    And no new checkout state is created

  Scenario: Shopper resumes checkout state from signed token
    Given a valid signed checkout token references a checkout state
    When the shopper requests checkout state
    Then the referenced state is returned
    And the token tenant matches the resolved tenant
