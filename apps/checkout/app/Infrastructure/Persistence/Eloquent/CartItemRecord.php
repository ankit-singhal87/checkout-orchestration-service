<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent record for a cart line item.
 */
final class CartItemRecord extends Model
{
    protected $table = 'cart_items';

    protected $guarded = [];

    /**
     * Variant added on this cart line.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariantRecord::class, 'product_variant_record_id');
    }
}
