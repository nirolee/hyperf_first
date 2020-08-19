<?php
declare(strict_types=1);

use Hyperf\View\Mode;

return [
    // 使用的渲染引擎
    'engine' => \Hyperf\View\Engine\BladeEngine::class,
    // 不填写则默认为 Task 模式，推荐使用 Task 模式
    'mode' => Mode::TASK,
    'config' => [
        ADMIN_SERVER => [
            // 若下列文件夹不存在请自行创建
            'view_path' => BASE_PATH . '/app/Admin/View/',
            'cache_path' => BASE_PATH . '/runtime/view/Admin/',
            'layout_name' => 'layouts.layouts',
        ]
    ],
];