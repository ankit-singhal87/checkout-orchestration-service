<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent record for a tenant-scoped product.
 */
final class ProductRecord extends Model
{
    protected $table = 'products';

    protected $guarded = [];

    /**
     * Cast structured product columns.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'badges' => 'array',
            'image' => 'array',
        ];
    }

    /**
     * Tenant that owns the product.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_record_id');
    }

    /**
     * Variants available for this product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariantRecord::class, 'product_record_id');
    }
}
