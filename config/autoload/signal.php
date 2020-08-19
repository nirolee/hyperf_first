<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'handlers' => [
        \Hyperf\AsyncQueue\Signal\DriverStopHandler::class, //异步队列进程安全关闭信号监听器
//        \Hyperf\Signal\Handler\WorkerStopHandler::class => PHP_INT_MIN // worker进程退出监听器
    ],
    'timeout' => 5.0,
];
