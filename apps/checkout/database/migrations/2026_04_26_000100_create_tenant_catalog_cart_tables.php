<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->unique();
            $table->string('shop_id')->unique();
            $table->string('host')->unique();
            $table->string('display_name');
            $table->string('currency', 3);
            $table->string('locale', 16);
            $table->json('brand');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_record_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('product_id');
            $table->string('category_id');
            $table->string('slug');
            $table->string('name');
            $table->text('description');
            $table->json('image');
            $table->json('badges');
            $table->timestamps();

            $table->unique(['tenant_record_id', 'product_id']);
            $table->unique(['tenant_record_id', 'slug']);
        });

        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_record_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('product_record_id')->constrained('products')->cascadeOnDelete();
            $table->string('variant_id');
            $table->json('options');
            $table->unsignedInteger('price_amount');
            $table->string('price_currency', 3);
            $table->unsignedInteger('stock_available');
            $table->string('stock_state');
            $table->timestamps();

            $table->unique(['tenant_record_id', 'variant_id']);
        });

        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_record_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('cart_id');
            $table->timestamps();

            $table->unique(['tenant_record_id', 'cart_id']);
        });

        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cart_record_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_variant_record_id')->constrained('product_variants')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['cart_record_id', 'product_variant_record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('tenants');
    }
};
