# C4: System Context

```mermaid
flowchart LR
  Shopper[Shopper] --> CheckoutMvp[CheckoutMVP]
  Customer[OptionalAuthenticatedCustomer] --> CheckoutMvp
  TenantAdmin[TenantAdmin] --> CheckoutMvp
  PlatformOperator[PlatformOperator] --> CheckoutMvp
  CheckoutMvp --> GrafanaCloud[GrafanaCloudOptional]
  CheckoutMvp --> AwsServices[AWSDeployModeOptional]
  CheckoutMvp --> GitLab[GitLabPrimaryRepoAndCI]
  GitHub[GitHubReadOnlyMirror] --> Shopper
```

## Notes

- Shoppers can complete checkout without logging in.
- Login/signup is optional before, during, or after checkout.
- GitLab is the primary repository and CI/CD platform.
- GitHub exists for read-only discoverability.
