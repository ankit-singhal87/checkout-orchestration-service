<?php

declare(strict_types=1);

namespace App\Domain\Tenant;

final readonly class TenantContext
{
    /**
     * @param list<string> $trustBadges
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
