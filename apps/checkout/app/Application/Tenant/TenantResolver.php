<?php

declare(strict_types=1);

namespace App\Application\Tenant;

use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\TenantRecord;

final class TenantResolver
{
    public function resolveHost(string $host): ?TenantContext
    {
        $record = TenantRecord::query()
            ->where('host', strtolower($host))
            ->first();

        if (! $record instanceof TenantRecord) {
            return null;
        }

        $brand = is_array($record->brand) ? $record->brand : [];

        return new TenantContext(
            recordId: (int) $record->id,
            tenantId: (string) $record->tenant_id,
            shopId: (string) $record->shop_id,
            host: (string) $record->host,
            displayName: (string) $record->display_name,
            currency: (string) $record->currency,
            locale: (string) $record->locale,
            primaryColor: (string) ($brand['primaryColor'] ?? '#111827'),
            trustBadges: array_values($brand['trustBadges'] ?? []),
        );
    }
}
