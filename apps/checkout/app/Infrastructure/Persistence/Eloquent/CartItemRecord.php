<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CartItemRecord extends Model
{
    protected $table = 'cart_items';

    protected $guarded = [];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariantRecord::class, 'product_variant_record_id');
    }
}
