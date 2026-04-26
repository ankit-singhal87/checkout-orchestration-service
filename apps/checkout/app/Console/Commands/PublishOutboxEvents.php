<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\OutboxEventRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Publishes committed outbox rows to the local Redis Stream.
 */
final class PublishOutboxEvents extends Command
{
    protected $signature = 'checkout:outbox:publish {--limit= : Maximum events to publish}';

    protected $description = 'Publish unpublished checkout outbox events to Redis Streams.';

    public function handle(): int
    {
        $stream = (string) config('outbox.redis_stream', 'checkout:events');
        $limit = (int) ($this->option('limit') ?: config('outbox.publish_batch_size', 50));

        if ($limit < 1) {
            $this->error('The publish limit must be greater than zero.');

            return self::FAILURE;
        }

        $events = OutboxEventRecord::query()
            ->whereNull('published_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($events->isEmpty()) {
            $this->info('No unpublished outbox events found.');

            return self::SUCCESS;
        }

        $published = 0;

        foreach ($events as $event) {
            try {
                $fields = [
                    'event_id' => (string) $event->event_id,
                    'event_type' => (string) $event->event_type,
                    'aggregate_type' => (string) $event->aggregate_type,
                    'aggregate_id' => (string) $event->aggregate_id,
                    'tenant_record_id' => (string) $event->tenant_record_id,
                    'payload' => json_encode($event->payload ?? [], JSON_THROW_ON_ERROR),
                    'created_at' => $event->created_at?->toJSON() ?? now()->toJSON(),
                ];

                Redis::connection()->command('xadd', [
                    $stream,
                    '*',
                    $fields,
                ]);

                $event->forceFill(['published_at' => now()])->save();
                $published++;
            } catch (Throwable $exception) {
                $this->error(sprintf(
                    'Failed to publish outbox event %s: %s',
                    (string) $event->event_id,
                    $exception->getMessage(),
                ));

                return self::FAILURE;
            }
        }

        $this->info(sprintf('Published %d outbox event(s).', $published));

        return self::SUCCESS;
    }
}
