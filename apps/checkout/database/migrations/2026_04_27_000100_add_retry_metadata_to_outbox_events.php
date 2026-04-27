<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add retry scheduling and poison-message metadata to durable outbox rows.
     */
    public function up(): void
    {
        if (! Schema::hasTable('outbox_events')) {
            return;
        }

        Schema::table('outbox_events', function (Blueprint $table): void {
            if (! Schema::hasColumn('outbox_events', 'publish_attempts')) {
                $table->unsignedSmallInteger('publish_attempts')->default(0)->after('published_at');
            }

            if (! Schema::hasColumn('outbox_events', 'next_publish_at')) {
                $table->timestamp('next_publish_at')->nullable()->after('publish_attempts');
            }

            if (! Schema::hasColumn('outbox_events', 'last_publish_attempted_at')) {
                $table->timestamp('last_publish_attempted_at')->nullable()->after('next_publish_at');
            }

            if (! Schema::hasColumn('outbox_events', 'poisoned_at')) {
                $table->timestamp('poisoned_at')->nullable()->after('last_publish_attempted_at');
            }

            if (! Schema::hasColumn('outbox_events', 'last_publish_error')) {
                $table->text('last_publish_error')->nullable()->after('poisoned_at');
            }
        });
    }

    /**
     * Remove retry metadata from outbox rows.
     */
    public function down(): void
    {
        if (! Schema::hasTable('outbox_events')) {
            return;
        }

        Schema::table('outbox_events', function (Blueprint $table): void {
            foreach ([
                'last_publish_error',
                'poisoned_at',
                'last_publish_attempted_at',
                'next_publish_at',
                'publish_attempts',
            ] as $column) {
                if (Schema::hasColumn('outbox_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
