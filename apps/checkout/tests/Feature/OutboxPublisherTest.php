<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\OutboxEventRecord;
use App\Infrastructure\Persistence\Eloquent\TenantRecord;
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
        ],
    ]);

    $connection = \Mockery::mock();
    $connection
        ->shouldReceive('command')
        ->once()
        ->with('xadd', \Mockery::on(function (array $arguments) use ($event): bool {
            return $arguments[0] === 'checkout:events'
                && $arguments[1] === '*'
                && $arguments[2]['event_id'] === $event->event_id
                && $arguments[2]['event_type'] === 'order.confirmed'
                && $arguments[2]['tenant_record_id'] === (string) $event->tenant_record_id;
        }))
        ->andReturn('1-0');

    Redis::shouldReceive('connection')->once()->andReturn($connection);

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

    $this
        ->artisan('checkout:outbox:publish', ['--limit' => 10])
        ->assertExitCode(1);

    expect($event->fresh()->published_at)->toBeNull();
});
