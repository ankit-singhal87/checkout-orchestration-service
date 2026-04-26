<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\Eloquent\ProductVariantRecord;
use App\Infrastructure\Persistence\Eloquent\TenantRecord;
use Illuminate\Database\Seeder;

final class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $fixtureRoot = is_dir('/seed/fixtures')
            ? '/seed/fixtures'
            : base_path('../../seed/fixtures');

        $tenantFixtures = json_decode((string) file_get_contents($fixtureRoot.'/tenants.json'), true, flags: JSON_THROW_ON_ERROR);
        $catalogFixtures = json_decode((string) file_get_contents($fixtureRoot.'/catalog-sample.json'), true, flags: JSON_THROW_ON_ERROR);

        foreach ($tenantFixtures as $tenantFixture) {
            TenantRecord::query()->updateOrCreate(
                ['tenant_id' => $tenantFixture['tenantId']],
                [
                    'shop_id' => $tenantFixture['shopId'],
                    'host' => $tenantFixture['host'],
                    'display_name' => $tenantFixture['displayName'],
                    'currency' => $tenantFixture['currency'],
                    'locale' => $tenantFixture['locale'],
                    'brand' => $tenantFixture['brand'],
                ],
            );
        }

        foreach ($catalogFixtures as $productFixture) {
            /** @var TenantRecord $tenant */
            $tenant = TenantRecord::query()
                ->where('tenant_id', $productFixture['tenantId'])
                ->firstOrFail();

            /** @var ProductRecord $product */
            $product = ProductRecord::query()->updateOrCreate(
                [
                    'tenant_record_id' => $tenant->id,
                    'product_id' => $productFixture['productId'],
                ],
                [
                    'category_id' => $productFixture['categoryId'],
                    'slug' => $productFixture['slug'],
                    'name' => $productFixture['name'],
                    'description' => $productFixture['description'],
                    'image' => $productFixture['image'],
                    'badges' => $productFixture['badges'],
                ],
            );

            foreach ($productFixture['variants'] as $variantFixture) {
                ProductVariantRecord::query()->updateOrCreate(
                    [
                        'tenant_record_id' => $tenant->id,
                        'variant_id' => $variantFixture['variantId'],
                    ],
                    [
                        'product_record_id' => $product->id,
                        'options' => $variantFixture['options'],
                        'price_amount' => $variantFixture['price']['amount'],
                        'price_currency' => $variantFixture['price']['currency'],
                        'stock_available' => $variantFixture['stock']['available'],
                        'stock_state' => $variantFixture['stock']['state'],
                    ],
                );
            }
        }
    }
}
