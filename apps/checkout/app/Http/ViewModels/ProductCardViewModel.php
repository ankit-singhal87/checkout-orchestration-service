<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

/**
 * Presentation model for a product card in listings.
 */
final readonly class ProductCardViewModel
{
    /**
     * Create a product card model.
     *
     * @param  list<string>  $badges
     */
    public function __construct(
        public string $slug,
        public string $name,
        public string $description,
        public string $imageAlt,
        public string $imageKey,
        public array $badges,
        public string $priceLabel,
    ) {}
}
