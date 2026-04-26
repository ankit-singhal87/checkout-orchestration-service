<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Root database seeder for local and test fixtures.
 */
final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed deterministic data needed by the checkout app.
     */
    public function run(): void
    {
        $this->call(DemoCatalogSeeder::class);
    }
}
