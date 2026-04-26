<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TenantRecord extends Model
{
    protected $table = 'tenants';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'brand' => 'array',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductRecord::class, 'tenant_record_id');
    }
}
