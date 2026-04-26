<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Catalog\StockState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent record for a sellable product variant.
 */
final class ProductVariantRecord extends Model
{
    protected $table = 'product_variants';

    protected $guarded = [];

    /**
     * Cast structured and closed-set variant columns.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'stock_state' => StockState::class,
        ];
    }

    /**
     * Product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductRecord::class, 'product_record_id');
    }

    /**
     * Tenant that owns the variant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_record_id');
    }
}
