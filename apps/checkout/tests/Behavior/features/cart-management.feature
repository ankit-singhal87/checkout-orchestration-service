Feature: Cart management
  Guest shoppers can add tenant-scoped product variants to the cart before checkout.

  Scenario: Shopper adds an in-stock variant to cart
    Given the "fashion-store" tenant is active
    And an in-stock variant exists for the fashion tenant
    When the shopper adds the variant to the cart
    Then the cart contains the selected variant
    And the cart summary shows tenant-specific trust badges

  Scenario: Shopper cannot add another tenant variant
    Given the "fashion-store" tenant is active
    And a sports tenant variant exists
    When the shopper adds the sports variant to the cart
    Then the cart is not changed
    And the API returns a tenant access Problem Details response
