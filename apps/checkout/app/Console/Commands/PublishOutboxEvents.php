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
            ->with('tenant')
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
                Redis::connection()->command('xadd', [
                    $stream,
                    '*',
                    $this->streamFields($event),
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

    /**
     * Build the Phase 3 Redis Stream envelope for a committed outbox row.
     *
     * @return array<string, string>
     */
    private function streamFields(OutboxEventRecord $event): array
    {
        $payload = $event->payload ?? [];
        $fields = [
            'eventId' => (string) $event->event_id,
            'eventType' => (string) $event->event_type,
            'schemaVersion' => (string) config('outbox.schema_version', 1),
            'aggregateType' => (string) $event->aggregate_type,
            'aggregateId' => (string) $event->aggregate_id,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'occurredAt' => $event->created_at?->toJSON() ?? now()->toJSON(),
        ];

        if ($event->tenant !== null) {
            $fields['tenantId'] = (string) $event->tenant->tenant_id;
            $fields['shopId'] = (string) $event->tenant->shop_id;
        }

        foreach (['traceId', 'requestId', 'traceparent'] as $key) {
            $value = $this->payloadContextValue($payload, $key);

            if ($value !== null) {
                $fields[$key] = $value;
            }
        }

        return $fields;
    }

    /**
     * Read correlation fields from top-level payload or nested context metadata.
     *
     * @param  array<string, mixed>  $payload
     */
    private function payloadContextValue(array $payload, string $key): ?string
    {
        $snakeKey = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $key));

        foreach ([$payload, $payload['context'] ?? null, $payload['metadata'] ?? null] as $source) {
            if (! is_array($source)) {
                continue;
            }

            foreach ([$key, $snakeKey] as $candidate) {
                $value = $source[$candidate] ?? null;

                if (is_string($value) && trim($value) !== '') {
                    return trim($value);
                }
            }
        }

        return null;
    }
}
