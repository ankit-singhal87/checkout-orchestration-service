<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create checkout, order, and outbox tables for dev databases created before the full Phase 1 migration landed.
     */
    public function up(): void
    {
        if (! Schema::hasTable('checkout_states')) {
            Schema::create('checkout_states', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_record_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('cart_record_id')->constrained('carts')->cascadeOnDelete();
                $table->string('checkout_id');
                $table->string('status');
                $table->json('shipping_address')->nullable();
                $table->string('shipping_option')->nullable();
                $table->string('payment_method')->nullable();
                $table->json('totals');
                $table->timestamps();

                $table->unique(['tenant_record_id', 'checkout_id']);
                $table->unique(['tenant_record_id', 'cart_record_id']);
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_record_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('checkout_state_record_id')->constrained('checkout_states')->cascadeOnDelete();
                $table->string('order_ref');
                $table->string('idempotency_key');
                $table->string('status');
                $table->json('cart_snapshot');
                $table->unsignedInteger('total_amount');
                $table->string('total_currency', 3);
                $table->timestamps();

                $table->unique(['tenant_record_id', 'order_ref']);
                $table->unique(['tenant_record_id', 'idempotency_key']);
            });
        }

        if (! Schema::hasTable('outbox_events')) {
            Schema::create('outbox_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_record_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('event_id')->unique();
                $table->string('event_type');
                $table->string('aggregate_type');
                $table->string('aggregate_id');
                $table->json('payload');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Drop checkout, order, and outbox tables created by this repair migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('checkout_states');
    }
};
