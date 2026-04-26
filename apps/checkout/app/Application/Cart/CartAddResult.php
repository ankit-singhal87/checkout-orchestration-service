<?php

declare(strict_types=1);

namespace App\Application\Cart;

final readonly class CartAddResult
{
    private function __construct(
        public bool $added,
        public ?string $problemType,
        public ?string $problemTitle,
        public ?int $problemStatus,
        public ?string $problemDetail,
    ) {}

    public static function added(): self
    {
        return new self(true, null, null, null, null);
    }

    public static function tenantAccessDenied(): self
    {
        return new self(
            false,
            '/problems/tenant-access-denied',
            'Tenant access denied',
            403,
            'The requested cart item is not available for this shop.',
        );
    }

    public static function outOfStock(): self
    {
        return new self(
            false,
            '/problems/validation-failed',
            'Cart item unavailable',
            422,
            'The selected variant is not available.',
        );
    }
}
