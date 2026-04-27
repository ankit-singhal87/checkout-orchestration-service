<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\OutboxEventRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Publishes committed outbox rows to the local Redis Stream.
 */
final class PublishOutboxEvents extends Command
{
    private const int MAX_ATTEMPTS = 3;

    private const int RETRY_DELAY_SECONDS = 60;

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
            ->whereNull('poisoned_at')
            ->where(function ($query): void {
                $query
                    ->whereNull('next_publish_at')
                    ->orWhere('next_publish_at', '<=', now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($events->isEmpty()) {
            $this->info('No publishable outbox events found.');

            return self::SUCCESS;
        }

        $published = 0;

        foreach ($events as $event) {
            $startedAt = microtime(true);

            try {
                Redis::connection()->command('xadd', [
                    $stream,
                    '*',
                    $this->streamFields($event),
                ]);

                $event->forceFill(['published_at' => now()])->save();
                Log::info('outbox_event_published', $this->observabilityContext(
                    event: $event,
                    status: 'published',
                    startedAt: $startedAt,
                    stream: $stream,
                ));
                $published++;
            } catch (Throwable $exception) {
                Log::error('outbox_event_publish_failed', $this->observabilityContext(
                    event: $event,
                    status: 'failed',
                    startedAt: $startedAt,
                    stream: $stream,
                    extra: [
                        'error' => mb_substr($exception->getMessage(), 0, 1000),
                    ],
                ));
                $this->error(sprintf(
                    'Failed to publish outbox event %s: %s',
                    (string) $event->event_id,
                    $exception->getMessage(),
                ));
                $this->recordFailedAttempt($event, $exception);

                return self::FAILURE;
            }
        }

        $this->info(sprintf('Published %d outbox event(s).', $published));

        return self::SUCCESS;
    }

    /**
     * Build safe structured context for worker log evidence.
     *
     * @return array<string, mixed>
     */
    private function observabilityContext(
        OutboxEventRecord $event,
        string $status,
        float $startedAt,
        string $stream,
        array $extra = [],
    ): array {
        return [
            ...$extra,
            'command' => 'checkout:outbox:publish',
            'processor' => 'checkout.outbox-publisher',
            'status' => $status,
            'event_id' => (string) $event->event_id,
            'event_type' => (string) $event->event_type,
            'tenant_id' => $event->tenant?->tenant_id,
            'shop_id' => $event->tenant?->shop_id,
            'stream' => $stream,
            'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    }

    /**
     * Record retry metadata and mark permanently failed rows as poison.
     */
    private function recordFailedAttempt(OutboxEventRecord $event, Throwable $exception): void
    {
        $attempts = ((int) $event->publish_attempts) + 1;
        $isPoison = $attempts >= self::MAX_ATTEMPTS;
        $attemptedAt = now();

        $event->forceFill([
            'publish_attempts' => $attempts,
            'last_publish_attempted_at' => $attemptedAt,
            'next_publish_at' => $isPoison ? null : $attemptedAt->copy()->addSeconds(self::RETRY_DELAY_SECONDS),
            'poisoned_at' => $isPoison ? $attemptedAt : null,
            'last_publish_error' => mb_substr($exception->getMessage(), 0, 1000),
        ])->save();
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
