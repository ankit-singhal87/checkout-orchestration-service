<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

final readonly class ProductCardViewModel
{
    /**
     * @param list<string> $badges
     */
    public function __construct(
        public string $slug,
        public string $name,
        public string $description,
        public string $imageAlt,
        public array $badges,
        public string $priceLabel,
    ) {}
}
