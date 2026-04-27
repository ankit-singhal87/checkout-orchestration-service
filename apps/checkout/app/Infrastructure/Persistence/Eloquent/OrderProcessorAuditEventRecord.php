<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rebuildable audit projection for consumed order processor events.
 */
final class OrderProcessorAuditEventRecord extends Model
{
    protected $table = 'order_processor_audit_events';

    protected $guarded = [];

    /**
     * Cast structured audit projection columns.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Tenant that owns the audit projection row.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_record_id');
    }
}
