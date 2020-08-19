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
    'AdminHttp' => Hyperf\HttpServer\Server::class, //后台应用server处理类
    'ApiHttp' => Hyperf\HttpServer\Server::class, //后台应用server处理类
    \Hyperf\View\RenderInterface::class => \App\Common\Library\MyRender::class,  //重写 view Render类
    \Hyperf\Contract\StdoutLoggerInterface::class => \App\Common\Library\log\MyLogFactory::class,
    \Psr\Log\LoggerInterface::class => \App\Common\Library\log\MyLogFactory::class,
];