<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CartRecord extends Model
{
    protected $table = 'carts';

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(CartItemRecord::class, 'cart_record_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_record_id');
    }
}
