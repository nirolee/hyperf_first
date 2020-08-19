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
    /*'http' => [
        \App\Api\Middleware\MyJwtAuthMiddleWare::class,  // jwt校验中间件
        \App\Api\Middleware\ResponseMiddleware::class //http请求输出结果封装中间件, 请保证此中间件为最后一个执行
    ],*/
    ADMIN_SERVER => [
        \Hyperf\Session\Middleware\SessionMiddleware::class, // 开启session
        \App\Admin\Middleware\LoginCheckMiddleware::class, // 登录判断中间件
        \App\Admin\Middleware\PermissionCheckMiddleware::class, // 权限校验中间件
    ]
];