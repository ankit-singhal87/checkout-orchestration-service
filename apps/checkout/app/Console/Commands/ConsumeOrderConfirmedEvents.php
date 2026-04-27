<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\OrderProcessorPoisonEventRecord;
use App\Infrastructure\Persistence\Eloquent\OrderProcessorProcessedEventRecord;
use App\Infrastructure\Persistence\Eloquent\TenantRecord;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use JsonException;
use Throwable;

/**
 * Consumes order-confirmed events for local replay-safe order processor work.
 */
final class ConsumeOrderConfirmedEvents extends Command
{
    private const PROCESSOR_NAME = 'checkout.order-processor';

    protected $signature = 'checkout:order-processor:consume
        {--limit=10 : Maximum Redis Stream messages to read}
        {--consumer=order-processor-local : Redis Stream consumer name}
        {--block-ms=0 : Milliseconds to block while waiting for messages}';

    protected $description = 'Consume order.confirmed events from Redis Streams idempotently.';

    public function handle(): int
    {
        $stream = (string) config('outbox.redis_stream', 'checkout:events');
        $limit = (int) $this->option('limit');
        $blockMilliseconds = (int) $this->option('block-ms');
        $consumer = trim((string) $this->option('consumer'));

        if ($limit < 1) {
            $this->error('The consume limit must be greater than zero.');

            return self::FAILURE;
        }

        if ($blockMilliseconds < 0 || $consumer === '') {
            $this->error('The block timeout must be zero or greater and the consumer name must be non-empty.');

            return self::FAILURE;
        }

        $messages = $this->readMessages(
            stream: $stream,
            consumer: $consumer,
            limit: $limit,
            blockMilliseconds: $blockMilliseconds,
        );

        if ($messages === []) {
            $this->info('No order processor messages found.');

            return self::SUCCESS;
        }

        $processed = 0;
        $duplicates = 0;
        $poisoned = 0;

        foreach ($messages as $message) {
            try {
                $result = $this->processMessage($stream, $message);
                Redis::connection()->command('xack', [$stream, self::PROCESSOR_NAME, $message['id']]);

                match ($result) {
                    'processed' => $processed++,
                    'duplicate' => $duplicates++,
                    'poisoned' => $poisoned++,
                };
            } catch (Throwable $exception) {
                $this->error(sprintf(
                    'Failed to consume stream message %s: %s',
                    $message['id'],
                    $exception->getMessage(),
                ));

                return self::FAILURE;
            }
        }

        $this->info(sprintf(
            'Order processor consumed %d message(s): %d processed, %d duplicate, %d poisoned.',
            count($messages),
            $processed,
            $duplicates,
            $poisoned,
        ));

        return self::SUCCESS;
    }

    /**
     * Read and normalize Redis Stream messages from the order processor consumer group.
     *
     * @return list<array{id: string, fields: array<string, string>}>
     */
    private function readMessages(string $stream, string $consumer, int $limit, int $blockMilliseconds): array
    {
        $response = Redis::connection()->command('xreadgroup', [
            'GROUP',
            self::PROCESSOR_NAME,
            $consumer,
            'COUNT',
            $limit,
            'BLOCK',
            $blockMilliseconds,
            'STREAMS',
            $stream,
            '>',
        ]);

        if (! is_array($response) || $response === []) {
            return [];
        }

        return $this->normalizeStreamResponse($stream, $response);
    }

    /**
     * Normalize common Predis and PhpRedis stream response shapes.
     *
     * @param  array<int|string, mixed>  $response
     * @return list<array{id: string, fields: array<string, string>}>
     */
    private function normalizeStreamResponse(string $stream, array $response): array
    {
        $streamMessages = $response[$stream] ?? null;

        if ($streamMessages === null && isset($response[0]) && is_array($response[0])) {
            $streamMessages = $response[0][1] ?? null;
        }

        if (! is_array($streamMessages)) {
            return [];
        }

        $messages = [];

        foreach ($streamMessages as $messageId => $message) {
            if (is_array($message) && array_is_list($message)) {
                $messageId = $message[0] ?? $messageId;
                $message = $message[1] ?? [];
            }

            if (! is_string($messageId) || ! is_array($message)) {
                continue;
            }

            $fields = [];

            foreach ($message as $field => $value) {
                if (is_int($field) && is_string($value) && array_key_exists($field + 1, $message)) {
                    $fields[$value] = (string) $message[$field + 1];

                    continue;
                }

                if (is_string($field)) {
                    $fields[$field] = (string) $value;
                }
            }

            $messages[] = [
                'id' => $messageId,
                'fields' => $fields,
            ];
        }

        return $messages;
    }

    /**
     * Persist the side-effect ledger or poison record for a single stream message.
     *
     * @param  array{id: string, fields: array<string, string>}  $message
     */
    private function processMessage(string $stream, array $message): string
    {
        $validationFailure = $this->validationFailure($message['fields']);

        if ($validationFailure !== null) {
            $this->recordPoisonEvent($stream, $message, $validationFailure);

            return 'poisoned';
        }

        $fields = $message['fields'];
        $tenant = TenantRecord::query()
            ->where('tenant_id', $fields['tenantId'])
            ->first();

        if (! $tenant instanceof TenantRecord) {
            $this->recordPoisonEvent($stream, $message, sprintf('Unknown tenant [%s].', $fields['tenantId']));

            return 'poisoned';
        }

        try {
            $payload = json_decode($fields['payload'], true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->recordPoisonEvent($stream, $message, 'Payload is not valid JSON.');

            return 'poisoned';
        }

        if (! is_array($payload)) {
            $this->recordPoisonEvent($stream, $message, 'Payload must decode to a JSON object.');

            return 'poisoned';
        }

        try {
            DB::transaction(function () use ($tenant, $fields, $payload): void {
                OrderProcessorProcessedEventRecord::query()->create([
                    'tenant_record_id' => $tenant->id,
                    'processor_name' => self::PROCESSOR_NAME,
                    'event_id' => $fields['eventId'],
                    'event_type' => $fields['eventType'],
                    'aggregate_type' => $fields['aggregateType'],
                    'aggregate_id' => $fields['aggregateId'],
                    'idempotency_key' => $fields['idempotencyKey'],
                    'correlation_id' => $fields['correlationId'] ?? null,
                    'trace_id' => $fields['traceId'] ?? null,
                    'request_id' => $fields['requestId'] ?? null,
                    'processed_at' => now(),
                    'payload' => $payload,
                ]);
            });
        } catch (QueryException $exception) {
            if ($this->isDuplicateKey($exception)) {
                return 'duplicate';
            }

            throw $exception;
        }

        return 'processed';
    }

    /**
     * Return a non-retriable validation failure reason when the envelope cannot be consumed.
     *
     * @param  array<string, string>  $fields
     */
    private function validationFailure(array $fields): ?string
    {
        foreach ([
            'eventId',
            'schemaVersion',
            'eventType',
            'tenantId',
            'aggregateType',
            'aggregateId',
            'idempotencyKey',
            'payload',
        ] as $requiredField) {
            if (trim($fields[$requiredField] ?? '') === '') {
                return sprintf('Missing required envelope field [%s].', $requiredField);
            }
        }

        if ($fields['schemaVersion'] !== '1') {
            return sprintf('Unsupported schema version [%s].', $fields['schemaVersion']);
        }

        if ($fields['eventType'] !== 'order.confirmed') {
            return sprintf('Unsupported event type [%s].', $fields['eventType']);
        }

        if ($fields['aggregateType'] !== 'order') {
            return sprintf('Unsupported aggregate type [%s].', $fields['aggregateType']);
        }

        return null;
    }

    /**
     * Persist a poison record before the stream message is acknowledged.
     *
     * @param  array{id: string, fields: array<string, string>}  $message
     */
    private function recordPoisonEvent(string $stream, array $message, string $reason): void
    {
        try {
            OrderProcessorPoisonEventRecord::query()->create([
                'stream' => $stream,
                'stream_message_id' => $message['id'],
                'consumer_group' => self::PROCESSOR_NAME,
                'event_id' => $message['fields']['eventId'] ?? null,
                'event_type' => $message['fields']['eventType'] ?? null,
                'failure_reason' => $reason,
                'attempt_count' => 1,
                'fields' => $message['fields'],
                'poisoned_at' => now(),
            ]);
        } catch (QueryException $exception) {
            if (! $this->isDuplicateKey($exception)) {
                throw $exception;
            }
        }
    }

    /**
     * Detect database unique-constraint violations across local database engines.
     */
    private function isDuplicateKey(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $driverCode = (string) ($exception->errorInfo[1] ?? '');

        return $sqlState === '23000' || $driverCode === '1062' || $driverCode === '19';
    }
}
