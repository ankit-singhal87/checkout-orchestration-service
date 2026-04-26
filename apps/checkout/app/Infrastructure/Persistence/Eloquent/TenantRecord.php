<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent record for a checkout tenant and shop host.
 */
final class TenantRecord extends Model
{
    protected $table = 'tenants';

    protected $guarded = [];

    /**
     * Cast structured tenant columns.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'brand' => 'array',
        ];
    }

    /**
     * Products owned by the tenant.
     */
    public function products(): HasMany
    {
        return $this->hasMany(ProductRecord::class, 'tenant_record_id');
    }
}
