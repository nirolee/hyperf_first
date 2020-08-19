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
return [
    'file' => [
        'handler' => [
//            'class' => Monolog\Handler\StreamHandler::class,
            'class' => \Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
//                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                'filename' => BASE_PATH . '/runtime/logs/app.log',
                'level' => env('LOG_LEVEL', \Monolog\Logger::INFO),
            ],
        ],
        'formatter' => [
            'class' => \App\Common\Library\log\MyLogFormatter::class,
        ],
    ],
    'stdout' => [
        'handler' => [
            'class' => Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => 'php://stdout',
                'level' => env('LOG_LEVEL', \Monolog\Logger::INFO),
            ],
        ],
        'formatter' => [
            'class' => \App\Common\Library\log\MyLogFormatter::class,
        ],
    ]
];
