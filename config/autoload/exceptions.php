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
    'handler' => [
        'http' => [
            \App\Api\Exception\Handler\ApiExceptionHandler::class,
            \Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler::class,
            \App\Common\Exception\Handler\AppExceptionHandler::class,
        ],
        ADMIN_SERVER => [
            \App\Admin\Exception\Handler\AdminExceptionHandler::class,
            \Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler::class,
            \App\Common\Exception\Handler\AppExceptionHandler::class,
        ]
    ],
];
