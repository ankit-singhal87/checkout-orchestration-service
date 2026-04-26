<?php

declare(strict_types=1);

it('adds request and trace correlation headers to public API responses', function () {
    $this
        ->withHeader('X-Request-Id', 'checkout-test-request')
        ->withHeader('traceparent', '00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01')
        ->getJson('http://fashion-demo.localhost/api/checkout/config')
        ->assertOk()
        ->assertHeader('X-Request-Id', 'checkout-test-request')
        ->assertHeader('X-Trace-Id', '4bf92f3577b34da6a3ce929d0e0e4736');
});

it('uses the trace correlation ID in validation Problem Details', function () {
    $this
        ->withHeader('X-Trace-Id', 'trace-for-problem-details')
        ->putJson('http://fashion-demo.localhost/api/checkout/state', [])
        ->assertUnprocessable()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertHeader('X-Trace-Id', 'trace-for-problem-details')
        ->assertJsonPath('traceId', 'trace-for-problem-details');
});
