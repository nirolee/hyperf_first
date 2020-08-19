<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

$heartbeat = (int) env('AMQP_POOL_HEARTBEAT');
return [
    'default' => [
        'host' => env('AMQP_HOST', 'localhost'),
        'port' => (int) env('AMQP_PORT', 5672),
        'user' => env('AMQP_USER', 'guest'),
        'password' => env('AMQP_PASSWORD', 'guest'),
        'vhost' => env('AMQP_VHOST', '/'),
        'concurrent' => [
            'limit' => 1,
        ],
        'pool' => [
            'min_connections' => (int) env('AMQP_POOL_MIN_CONNECTIONS'),
            'max_connections' => (int) env('AMQP_POOL_MAX_CONNECTIONS'),
            'connect_timeout' => (float) env('AMQP_POOL_CONNECTIONS_TIMEOUT'),
            'wait_timeout' => (float) env('AMQP_POOL_WAIT_TIMEOUT'),
            'heartbeat' => $heartbeat,
        ],
        'params' => [
            'insist' => false,
            'login_method' => 'AMQPLAIN',
            'login_response' => null,
            'locale' => 'en_US',
            'connection_timeout' => (float) env('AMQP_POOL_CONNECTIONS_TIMEOUT'),
            'read_write_timeout' => (float) $heartbeat * 2,
            'context' => null,
            'keepalive' => (bool) env('AMQP_PARAMS_KEEPALIVE'),
            'heartbeat' => $heartbeat,
            'close_on_destruct' => true,
        ],
    ],
];
