<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Processed-event ledger for idempotent order processor side effects.
 */
final class OrderProcessorProcessedEventRecord extends Model
{
    protected $table = 'order_processor_processed_events';

    protected $guarded = [];

    /**
     * Cast structured processor event columns.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Tenant that owns the processed event.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_record_id');
    }
}
