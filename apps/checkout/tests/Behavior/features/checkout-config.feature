Feature: Checkout configuration
  Public checkout configuration is resolved by tenant context and safe to cache.

  Scenario: Shopper reads tenant checkout configuration
    Given the shopper visits the fashion tenant host
    When the shopper requests checkout configuration
    Then supported countries are returned
    And payment method hints are returned
    And only public tenant branding is included

  Scenario: Unknown tenant host is rejected
    Given the shopper visits an unknown host
    When the shopper requests checkout configuration
    Then no tenant data is returned
    And the API returns a tenant access Problem Details response
