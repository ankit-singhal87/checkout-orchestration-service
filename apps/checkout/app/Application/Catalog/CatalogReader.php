<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use Illuminate\Database\Eloquent\Collection;

final class CatalogReader
{
    /**
     * @return Collection<int, ProductRecord>
     */
    public function productsForTenant(TenantContext $tenant): Collection
    {
        return ProductRecord::query()
            ->where('tenant_record_id', $tenant->recordId)
            ->with('variants')
            ->orderBy('name')
            ->get();
    }

    public function productBySlug(TenantContext $tenant, string $slug): ?ProductRecord
    {
        $product = ProductRecord::query()
            ->where('tenant_record_id', $tenant->recordId)
            ->where('slug', $slug)
            ->with('variants')
            ->first();

        return $product instanceof ProductRecord ? $product : null;
    }
}
