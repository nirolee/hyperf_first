<?php
declare(strict_types=1);

return [
    // 是否开启定时任务
    'enable' => (bool) env('ENABLE_CRONTAB', 'false'),
    'crontab' => [
    ]
];