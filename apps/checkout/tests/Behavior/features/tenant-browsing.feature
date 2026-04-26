Feature: Tenant-aware browsing
  Shoppers see catalog and branding for the resolved tenant only.

  Scenario: Shopper browses fashion tenant products
    Given the shopper visits the fashion tenant host
    When the shopper opens the shop page
    Then only fashion tenant products are shown
    And fashion tenant branding is shown
    And no sports tenant products are visible

  Scenario: Shopper browses sports tenant products
    Given the shopper visits the sports tenant host
    When the shopper opens the shop page
    Then only sports tenant products are shown
    And sports tenant branding is shown
    And no fashion tenant products are visible
