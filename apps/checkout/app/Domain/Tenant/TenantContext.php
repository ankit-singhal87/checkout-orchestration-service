<?php

declare(strict_types=1);

namespace App\Domain\Tenant;

/**
 * Immutable tenant identity and branding resolved for a request.
 */
final readonly class TenantContext
{
    /**
     * Create a tenant context for application and presentation layers.
     *
     * @param  list<string>  $trustBadges
     */
    public function __construct(
        public int $recordId,
        public string $tenantId,
        public string $shopId,
        public string $host,
        public string $displayName,
        public string $currency,
        public string $locale,
        public string $primaryColor,
        public array $trustBadges,
    ) {}
}
