<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\OutboxEventRecord;
use App\Infrastructure\Persistence\Eloquent\TenantRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

it('publishes unpublished outbox events to Redis Streams', function () {
    $tenant = TenantRecord::query()->where('tenant_id', 'fashion-store')->firstOrFail();

    /** @var OutboxEventRecord $event */
    $event = OutboxEventRecord::query()->create([
        'tenant_record_id' => $tenant->id,
        'event_id' => checkoutTestNamespace('order-confirmed-event'),
        'event_type' => 'order.confirmed',
        'aggregate_type' => 'order',
        'aggregate_id' => checkoutTestNamespace('order-ref'),
        'payload' => [
            'orderRef' => checkoutTestNamespace('order-ref'),
            'tenant' => 'fashion-store',
            'correlationId' => checkoutTestNamespace('checkout-id'),
            'causationId' => checkoutTestNamespace('checkout-confirmation-command'),
            'idempotencyKey' => checkoutTestNamespace('order-confirmed-business-key'),
            'context' => [
                'request_id' => 'request-from-outbox-context',
                'traceId' => 'trace-from-outbox-context',
                'traceparent' => '00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01',
            ],
        ],
    ]);

    $connection = \Mockery::mock();
    $connection
        ->shouldReceive('command')
        ->once()
        ->with('xadd', \Mockery::on(function (array $arguments) use ($event): bool {
            if ($arguments[0] !== 'checkout:events' || $arguments[1] !== '*') {
                return false;
            }

            $fields = $arguments[2];
            $payload = json_decode((string) $fields['payload'], true, flags: JSON_THROW_ON_ERROR);

            return $fields['eventId'] === $event->event_id
                && $fields['eventType'] === 'order.confirmed'
                && $fields['schemaVersion'] === '1'
                && $fields['aggregateType'] === 'order'
                && $fields['aggregateId'] === $event->aggregate_id
                && $fields['tenantId'] === 'fashion-store'
                && $fields['shopId'] === 'fashion-main'
                && $fields['occurredAt'] === $event->created_at?->toJSON()
                && $fields['correlationId'] === checkoutTestNamespace('checkout-id')
                && $fields['causationId'] === checkoutTestNamespace('checkout-confirmation-command')
                && $fields['idempotencyKey'] === checkoutTestNamespace('order-confirmed-business-key')
                && $fields['requestId'] === 'request-from-outbox-context'
                && $fields['traceId'] === 'trace-from-outbox-context'
                && $fields['traceparent'] === '00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01'
                && ! array_key_exists('tenant_record_id', $fields)
                && $payload['orderRef'] === checkoutTestNamespace('order-ref');
        }))
        ->andReturn('1-0');

    Redis::shouldReceive('connection')->once()->andReturn($connection);

    Log::shouldReceive('info')
        ->once()
        ->with('outbox_event_published', \Mockery::on(function (array $context) use ($event): bool {
            return $context['command'] === 'checkout:outbox:publish'
                && $context['processor'] === 'checkout.outbox-publisher'
                && $context['status'] === 'published'
                && $context['event_id'] === $event->event_id
                && $context['event_type'] === 'order.confirmed'
                && $context['tenant_id'] === 'fashion-store'
                && $context['shop_id'] === 'fashion-main'
                && $context['stream'] === 'checkout:events'
                && is_int($context['latency_ms'])
                && ! array_key_exists('tenant_record_id', $context);
        }));

    $this
        ->artisan('checkout:outbox:publish', ['--limit' => 10])
        ->assertExitCode(0);

    expect($event->fresh()->published_at)->not->toBeNull();
});

it('leaves outbox events unpublished when Redis publishing fails', function () {
    $tenant = TenantRecord::query()->where('tenant_id', 'fashion-store')->firstOrFail();

    /** @var OutboxEventRecord $event */
    $event = OutboxEventRecord::query()->create([
        'tenant_record_id' => $tenant->id,
        'event_id' => checkoutTestNamespace('failed-order-confirmed-event'),
        'event_type' => 'order.confirmed',
        'aggregate_type' => 'order',
        'aggregate_id' => checkoutTestNamespace('failed-order-ref'),
        'payload' => [
            'orderRef' => checkoutTestNamespace('failed-order-ref'),
            'tenant' => 'fashion-store',
        ],
    ]);

    $connection = \Mockery::mock();
    $connection
        ->shouldReceive('command')
        ->once()
        ->andThrow(new RuntimeException('Redis unavailable'));

    Redis::shouldReceive('connection')->once()->andReturn($connection);

    Log::shouldReceive('error')
        ->once()
        ->with('outbox_event_publish_failed', \Mockery::on(function (array $context) use ($event): bool {
            return $context['command'] === 'checkout:outbox:publish'
                && $context['processor'] === 'checkout.outbox-publisher'
                && $context['status'] === 'failed'
                && $context['event_id'] === $event->event_id
                && $context['tenant_id'] === 'fashion-store'
                && $context['error'] === 'Redis unavailable'
                && is_int($context['latency_ms'])
                && ! array_key_exists('tenant_record_id', $context);
        }));

    $this
        ->artisan('checkout:outbox:publish', ['--limit' => 10])
        ->assertExitCode(1);

    $failedEvent = $event->fresh();

    expect($failedEvent->published_at)->toBeNull()
        ->and($failedEvent->publish_attempts)->toBe(1)
        ->and($failedEvent->last_publish_attempted_at)->not->toBeNull()
        ->and($failedEvent->next_publish_at)->not->toBeNull()
        ->and($failedEvent->poisoned_at)->toBeNull()
        ->and($failedEvent->last_publish_error)->toBe('Redis unavailable');
});

it('marks an outbox event as poison after the final retry attempt fails', function () {
    $tenant = TenantRecord::query()->where('tenant_id', 'fashion-store')->firstOrFail();

    /** @var OutboxEventRecord $event */
    $event = OutboxEventRecord::query()->create([
        'tenant_record_id' => $tenant->id,
        'event_id' => checkoutTestNamespace('poison-order-confirmed-event'),
        'event_type' => 'order.confirmed',
        'aggregate_type' => 'order',
        'aggregate_id' => checkoutTestNamespace('poison-order-ref'),
        'payload' => [
            'orderRef' => checkoutTestNamespace('poison-order-ref'),
            'tenant' => 'fashion-store',
        ],
        'publish_attempts' => 2,
        'next_publish_at' => now()->subMinute(),
    ]);

    $connection = \Mockery::mock();
    $connection
        ->shouldReceive('command')
        ->once()
        ->andThrow(new RuntimeException('Malformed event payload'));

    Redis::shouldReceive('connection')->once()->andReturn($connection);

    $this
        ->artisan('checkout:outbox:publish', ['--limit' => 10])
        ->assertExitCode(1);

    $poisonEvent = $event->fresh();

    expect($poisonEvent->published_at)->toBeNull()
        ->and($poisonEvent->publish_attempts)->toBe(3)
        ->and($poisonEvent->last_publish_attempted_at)->not->toBeNull()
        ->and($poisonEvent->next_publish_at)->toBeNull()
        ->and($poisonEvent->poisoned_at)->not->toBeNull()
        ->and($poisonEvent->last_publish_error)->toBe('Malformed event payload');
});

it('skips poison and future scheduled outbox events', function () {
    $tenant = TenantRecord::query()->where('tenant_id', 'fashion-store')->firstOrFail();

    OutboxEventRecord::query()->create([
        'tenant_record_id' => $tenant->id,
        'event_id' => checkoutTestNamespace('already-poison-event'),
        'event_type' => 'order.confirmed',
        'aggregate_type' => 'order',
        'aggregate_id' => checkoutTestNamespace('already-poison-order-ref'),
        'payload' => [
            'orderRef' => checkoutTestNamespace('already-poison-order-ref'),
            'tenant' => 'fashion-store',
        ],
        'publish_attempts' => 3,
        'poisoned_at' => now()->subMinute(),
        'last_publish_error' => 'Previous permanent failure',
    ]);

    OutboxEventRecord::query()->create([
        'tenant_record_id' => $tenant->id,
        'event_id' => checkoutTestNamespace('future-scheduled-event'),
        'event_type' => 'order.confirmed',
        'aggregate_type' => 'order',
        'aggregate_id' => checkoutTestNamespace('future-scheduled-order-ref'),
        'payload' => [
            'orderRef' => checkoutTestNamespace('future-scheduled-order-ref'),
            'tenant' => 'fashion-store',
        ],
        'publish_attempts' => 1,
        'next_publish_at' => now()->addMinute(),
        'last_publish_error' => 'Temporary failure',
    ]);

    Redis::shouldReceive('connection')->never();

    $this
        ->artisan('checkout:outbox:publish', ['--limit' => 10])
        ->assertExitCode(0);
});
