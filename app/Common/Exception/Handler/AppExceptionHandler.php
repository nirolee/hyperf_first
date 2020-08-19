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

namespace App\Common\Exception\Handler;

use App\Common\Helper\ToolsHelper;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{

    public function handle(Throwable $throwable, ResponseInterface $response) : ResponseInterface
    {
        $message = sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile())."\n".$throwable->getTraceAsString();
        ToolsHelper::log($message, 'app', ['type' => 'error']);
        if (env('DEV_MODE', false)) {
            $errorInfo = $message;
        } else {
            $errorInfo = 'Internal Server Error.';
        }
        return $response->withStatus(500)->withBody(new SwooleStream($errorInfo));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
