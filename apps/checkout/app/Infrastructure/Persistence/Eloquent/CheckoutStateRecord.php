<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Checkout\CheckoutStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Eloquent record for a resumable tenant-scoped checkout state.
 */
final class CheckoutStateRecord extends Model
{
    protected $table = 'checkout_states';

    protected $guarded = [];

    /**
     * Cast structured and closed-set checkout columns.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'status' => CheckoutStatus::class,
            'totals' => 'array',
        ];
    }

    /**
     * Cart snapshot feeding this checkout state.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(CartRecord::class, 'cart_record_id');
    }

    /**
     * Order created from this checkout state.
     */
    public function order(): HasOne
    {
        return $this->hasOne(OrderRecord::class, 'checkout_state_record_id');
    }

    /**
     * Tenant that owns this checkout state.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_record_id');
    }
}
