<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Hyperf\Server\Server;
use Hyperf\Server\SwooleEvent;

$developMode = env('DEV_MODE', false);
if ($developMode) {
    $workerNum = 1;
} else {
    $workerNum = swoole_cpu_num();
}
return [
    'mode' => SWOOLE_PROCESS,
    'servers' => [
        [
            'name' => ADMIN_SERVER,
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => intval(env('ADMIN_LISTEN_PORT')),
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                SwooleEvent::ON_REQUEST => ['AdminHttp', 'onRequest'],
            ],
        ],
        /*[
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => intval(env('LISTEN_PORT')),
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                SwooleEvent::ON_REQUEST => ['ApiHttp', 'onRequest'],
            ],
        ],*/
    ],
    'settings' => [
        'enable_coroutine' => true,
        'worker_num' => $workerNum,
        'pid_file' => BASE_PATH . '/runtime/hyperf.pid',
        'open_tcp_nodelay' => true,
        'max_coroutine' => 100000,
        'open_http2_protocol' => true,
        'max_request' => 100000,
        'socket_buffer_size' => 2 * 1024 * 1024,
        'package_max_length' => 10 * 1024 * 1024, // 请求body大小限制, 10M
        'daemonize' => env('SERVER_DAEMON', 0),
        'task_worker_num' => env('TASK_WORK_NUM', 8),
        // 因为 `Task` 主要处理无法协程化的方法，所以这里推荐设为 `false`，避免协程下出现数据混淆的情况
        'task_enable_coroutine' => false,
        // 配置静态资源
//        'document_root' => BASE_PATH . '/resource',
//        'static_handler_locations' => ['/'],
//        'enable_static_handler' => true,
    ],
    'callbacks' => [
        SwooleEvent::ON_BEFORE_START => [Hyperf\Framework\Bootstrap\ServerStartCallback::class, 'beforeStart'],
        SwooleEvent::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
        SwooleEvent::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
        // Task callbacks
        SwooleEvent::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        SwooleEvent::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'],
    ],
];
