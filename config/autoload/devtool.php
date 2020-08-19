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
    'generator' => [
        'amqp' => [
            'consumer' => [
                'namespace' => 'App\\Admin\\Amqp\\Consumer',
            ],
            'producer' => [
                'namespace' => 'App\\Admin\\Amqp\\Producer',
            ],
        ],
        'aspect' => [
            'namespace' => 'App\\Common\\Aspect',
        ],
        'command' => [
            'namespace' => 'App\\Admin\\Command',
        ],
        'controller' => [
            'namespace' => 'App\\Admin\\Controller',
        ],
        'job' => [
            'namespace' => 'App\\Admin\\Job',
        ],
        'listener' => [
            'namespace' => 'App\\Common\\Listener',
        ],
        'middleware' => [
            'namespace' => 'App\\Common\\Middleware',
        ],
        'Process' => [
            'namespace' => 'App\\Common\\Processes',
        ],
    ],
];
