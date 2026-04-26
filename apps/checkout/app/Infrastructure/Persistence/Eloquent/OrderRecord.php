<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Order\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent record for an order committed from checkout confirmation.
 */
final class OrderRecord extends Model
{
    protected $table = 'orders';

    protected $guarded = [];

    /**
     * Cast structured and closed-set order columns.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cart_snapshot' => 'array',
            'status' => OrderStatus::class,
        ];
    }

    /**
     * Checkout state that produced the order.
     */
    public function checkoutState(): BelongsTo
    {
        return $this->belongsTo(CheckoutStateRecord::class, 'checkout_state_record_id');
    }

    /**
     * Tenant that owns the order.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_record_id');
    }
}
