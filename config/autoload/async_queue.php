<?php
declare(strict_types=1);

return [
    \App\Common\Constants\SysEnum::DEFAULT_QUEUE => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'redis' => [
            'pool' => 'default'
        ],
        'channel' => \App\Common\Constants\SysEnum::DEFAULT_QUEUE,
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 10,
        'processes' => 1,
        'concurrent' => [
            'limit' => 5,
        ],
    ],
];