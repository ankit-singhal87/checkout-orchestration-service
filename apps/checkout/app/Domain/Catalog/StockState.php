<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Stock states imported from catalog fixtures and persistence.
 */
enum StockState: string
{
    case InStock = 'in_stock';
    case LowStock = 'low_stock';
    case OutOfStock = 'out_of_stock';

    /**
     * Convert persisted stock state values into the enum.
     */
    public static function fromStorage(self|string|null $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom((string) $value) ?? self::OutOfStock;
    }

    /**
     * Determine whether the stock state and quantity allow cart additions.
     */
    public function canAddToCart(int $available): bool
    {
        return $available > 0 && $this !== self::OutOfStock;
    }
}
