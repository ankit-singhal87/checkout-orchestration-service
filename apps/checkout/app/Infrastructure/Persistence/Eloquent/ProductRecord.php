<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductRecord extends Model
{
    protected $table = 'products';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'badges' => 'array',
            'image' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_record_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariantRecord::class, 'product_record_id');
    }
}
