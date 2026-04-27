<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create local idempotency and poison records for the order processor worker.
     */
    public function up(): void
    {
        Schema::create('order_processor_processed_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_record_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('processor_name');
            $table->string('event_id');
            $table->string('event_type');
            $table->string('aggregate_type');
            $table->string('aggregate_id');
            $table->string('idempotency_key');
            $table->string('correlation_id')->nullable();
            $table->string('trace_id')->nullable();
            $table->string('request_id')->nullable();
            $table->timestamp('processed_at');
            $table->json('payload');
            $table->timestamps();

            $table->unique(['tenant_record_id', 'processor_name', 'event_id'], 'order_processor_event_dedupe_unique');
            $table->unique(['tenant_record_id', 'processor_name', 'idempotency_key'], 'order_processor_business_dedupe_unique');
        });

        Schema::create('order_processor_poison_events', function (Blueprint $table): void {
            $table->id();
            $table->string('stream');
            $table->string('stream_message_id');
            $table->string('consumer_group');
            $table->string('event_id')->nullable();
            $table->string('event_type')->nullable();
            $table->string('failure_reason');
            $table->unsignedInteger('attempt_count')->default(1);
            $table->json('fields');
            $table->timestamp('poisoned_at');
            $table->timestamps();

            $table->unique(['consumer_group', 'stream', 'stream_message_id'], 'order_processor_poison_message_unique');
        });
    }

    /**
     * Drop local order processor idempotency and poison records.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_processor_poison_events');
        Schema::dropIfExists('order_processor_processed_events');
    }
};
