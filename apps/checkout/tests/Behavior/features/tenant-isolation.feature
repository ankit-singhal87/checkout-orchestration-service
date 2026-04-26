Feature: Tenant isolation
  Tenant-scoped data must not leak between shops.

  Scenario: Cart from one tenant cannot initialize checkout in another tenant
    Given a cart belongs to the fashion tenant
    When a checkout state creation request is made for the sports tenant
    Then checkout state is not created
    And the API returns a tenant access Problem Details response

  Scenario: Product slugs are scoped by tenant
    Given both tenants have a product with the same slug
    When the shopper opens the product page on the fashion tenant
    Then the fashion tenant product is shown
    And the sports tenant product is not read
