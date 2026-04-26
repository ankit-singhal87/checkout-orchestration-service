<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Eloquent record for a tenant-scoped shopper cart.
 */
final class CartRecord extends Model
{
    protected $table = 'carts';

    protected $guarded = [];

    /**
     * Items currently in the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItemRecord::class, 'cart_record_id');
    }

    /**
     * Tenant that owns the cart.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_record_id');
    }

    /**
     * Checkout state created from the cart.
     */
    public function checkoutState(): HasOne
    {
        return $this->hasOne(CheckoutStateRecord::class, 'cart_record_id');
    }
}
