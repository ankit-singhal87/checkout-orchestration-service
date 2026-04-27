<?php

declare(strict_types=1);

return [
    'schema_version' => (int) env('OUTBOX_SCHEMA_VERSION', 1),
    'redis_stream' => env('OUTBOX_REDIS_STREAM', 'checkout:events'),
    'publish_batch_size' => (int) env('OUTBOX_PUBLISH_BATCH_SIZE', 50),
];
