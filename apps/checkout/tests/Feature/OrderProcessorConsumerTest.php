<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\OrderProcessorAuditEventRecord;
use App\Infrastructure\Persistence\Eloquent\OrderProcessorPoisonEventRecord;
use App\Infrastructure\Persistence\Eloquent\OrderProcessorProcessedEventRecord;
use Illuminate\Support\Facades\Redis;

it('processes an order confirmed stream envelope idempotently', function () {
    $fields = orderConfirmedEnvelopeFields('idempotent-order-confirmed');

    $connection = Mockery::mock();
    $connection
        ->shouldReceive('command')
        ->once()
        ->with('xreadgroup', Mockery::on(function (array $arguments): bool {
            return $arguments === [
                'GROUP',
                'checkout.order-processor',
                'order-processor-local',
                'COUNT',
                10,
                'BLOCK',
                0,
                'STREAMS',
                'checkout:events',
                '>',
            ];
        }))
        ->andReturn([
            'checkout:events' => [
                '1-0' => $fields,
                '1-1' => $fields,
            ],
        ]);

    $connection
        ->shouldReceive('command')
        ->twice()
        ->with('xack', Mockery::on(function (array $arguments): bool {
            return $arguments[0] === 'checkout:events'
                && $arguments[1] === 'checkout.order-processor'
                && in_array($arguments[2], ['1-0', '1-1'], true);
        }))
        ->andReturn(1);

    Redis::shouldReceive('connection')->times(3)->andReturn($connection);

    $this
        ->artisan('checkout:order-processor:consume')
        ->expectsOutput('Order processor consumed 2 message(s): 1 processed, 1 duplicate, 0 poisoned.')
        ->assertExitCode(0);

    $auditProjection = OrderProcessorAuditEventRecord::query()->first();

    expect(OrderProcessorProcessedEventRecord::query()->count())->toBe(1)
        ->and(OrderProcessorProcessedEventRecord::query()->first()?->event_id)->toBe($fields['eventId'])
        ->and(OrderProcessorAuditEventRecord::query()->count())->toBe(1)
        ->and($auditProjection?->event_id)->toBe($fields['eventId'])
        ->and($auditProjection?->idempotency_key)->toBe($fields['idempotencyKey'])
        ->and($auditProjection?->order_ref)->toBe($fields['aggregateId'])
        ->and(OrderProcessorPoisonEventRecord::query()->count())->toBe(0);
});

it('dedupes duplicate business idempotency keys across event ids', function () {
    $first = orderConfirmedEnvelopeFields('same-business-key-first');
    $second = array_merge(
        orderConfirmedEnvelopeFields('same-business-key-second'),
        ['idempotencyKey' => $first['idempotencyKey']],
    );

    $connection = Mockery::mock();
    $connection
        ->shouldReceive('command')
        ->once()
        ->with('xreadgroup', Mockery::type('array'))
        ->andReturn([
            'checkout:events' => [
                '2-0' => $first,
                '2-1' => $second,
            ],
        ]);

    $connection
        ->shouldReceive('command')
        ->twice()
        ->with('xack', Mockery::type('array'))
        ->andReturn(1);

    Redis::shouldReceive('connection')->times(3)->andReturn($connection);

    $this
        ->artisan('checkout:order-processor:consume')
        ->expectsOutput('Order processor consumed 2 message(s): 1 processed, 1 duplicate, 0 poisoned.')
        ->assertExitCode(0);

    expect(OrderProcessorProcessedEventRecord::query()->count())->toBe(1)
        ->and(OrderProcessorProcessedEventRecord::query()->first()?->event_id)->toBe($first['eventId'])
        ->and(OrderProcessorAuditEventRecord::query()->count())->toBe(1)
        ->and(OrderProcessorAuditEventRecord::query()->first()?->event_id)->toBe($first['eventId']);
});

it('records and acknowledges poison envelopes explicitly', function () {
    $fields = orderConfirmedEnvelopeFields('poison-order-confirmed');
    unset($fields['idempotencyKey']);

    $connection = Mockery::mock();
    $connection
        ->shouldReceive('command')
        ->once()
        ->with('xreadgroup', Mockery::type('array'))
        ->andReturn([
            'checkout:events' => [
                '3-0' => $fields,
            ],
        ]);

    $connection
        ->shouldReceive('command')
        ->once()
        ->with('xack', ['checkout:events', 'checkout.order-processor', '3-0'])
        ->andReturn(1);

    Redis::shouldReceive('connection')->twice()->andReturn($connection);

    $this
        ->artisan('checkout:order-processor:consume')
        ->expectsOutput('Order processor consumed 1 message(s): 0 processed, 0 duplicate, 1 poisoned.')
        ->assertExitCode(0);

    $poison = OrderProcessorPoisonEventRecord::query()->first();

    expect(OrderProcessorProcessedEventRecord::query()->count())->toBe(0)
        ->and(OrderProcessorAuditEventRecord::query()->count())->toBe(0)
        ->and($poison)->not->toBeNull()
        ->and($poison?->stream_message_id)->toBe('3-0')
        ->and($poison?->failure_reason)->toBe('Missing required envelope field [idempotencyKey].');
});

/**
 * Build a Redis Stream field map for an order.confirmed envelope.
 *
 * @return array<string, string>
 */
function orderConfirmedEnvelopeFields(string $name): array
{
    $orderRef = checkoutTestNamespace($name.'-order-ref');
    $eventId = checkoutTestNamespace($name.'-event-id');

    return [
        'eventId' => $eventId,
        'schemaVersion' => '1',
        'eventType' => 'order.confirmed',
        'occurredAt' => '2026-04-27T00:00:00Z',
        'tenantId' => 'fashion-store',
        'aggregateType' => 'order',
        'aggregateId' => $orderRef,
        'correlationId' => checkoutTestNamespace($name.'-correlation'),
        'causationId' => checkoutTestNamespace($name.'-causation'),
        'idempotencyKey' => checkoutTestNamespace($name.'-business-key'),
        'traceId' => checkoutTestNamespace($name.'-trace'),
        'requestId' => checkoutTestNamespace($name.'-request'),
        'payload' => json_encode([
            'orderRef' => $orderRef,
            'tenant' => 'fashion-store',
        ], JSON_THROW_ON_ERROR),
    ];
}
