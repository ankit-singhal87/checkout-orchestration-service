<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Poison-message ledger for order processor events that cannot be consumed.
 */
final class OrderProcessorPoisonEventRecord extends Model
{
    protected $table = 'order_processor_poison_events';

    protected $guarded = [];

    /**
     * Cast structured poison event columns.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'poisoned_at' => 'datetime',
        ];
    }
}
