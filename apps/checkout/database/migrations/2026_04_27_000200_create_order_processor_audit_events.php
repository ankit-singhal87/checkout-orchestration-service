<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the order processor audit projection table.
     */
    public function up(): void
    {
        Schema::create('order_processor_audit_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_record_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('processor_name');
            $table->string('event_id');
            $table->string('event_type');
            $table->string('aggregate_type');
            $table->string('aggregate_id');
            $table->string('idempotency_key');
            $table->string('order_ref');
            $table->string('correlation_id')->nullable();
            $table->string('trace_id')->nullable();
            $table->string('request_id')->nullable();
            $table->timestamp('recorded_at');
            $table->json('payload');
            $table->timestamps();

            $table->unique(['tenant_record_id', 'processor_name', 'event_id'], 'order_processor_audit_event_unique');
            $table->unique(['tenant_record_id', 'processor_name', 'idempotency_key'], 'order_processor_audit_business_unique');
        });
    }

    /**
     * Drop the order processor audit projection table.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_processor_audit_events');
    }
};
