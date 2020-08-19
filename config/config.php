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
    // 生产环境使用 prod 值
    'app_env' => env('APP_ENV', 'dev'),
    // 是否使用注解扫描缓存
    'scan_cacheable' => env('SCAN_CACHEABLE', false),
    'app_name' => env('APP_NAME', 'skeleton'),
    'project_name' => env('PROJECT_NAME', 'skeleton'),
    'admin_password_salt' => env('ADMIN_PASSWORD_SALT', '><!?M@#)!_(FI/'),
    'log_engine' => env('LOG_ENGINE', 'file'),
    'log_switch' => env('LOG_SWITCH', true),
];
