<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Tenant\TenantContext;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reads tenant-scoped catalog data for checkout views.
 */
final readonly class CatalogReader
{
    /**
     * List products available to a tenant.
     *
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

    /**
     * Find a product by tenant and slug.
     */
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
