<?php
declare(strict_types=1);

namespace App\Admin\Exception\Handler;


use App\Admin\Exception\BusinessException;
use App\Common\Helper\ToolsHelper;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AdminExceptionHandler extends ExceptionHandler
{

    /**
     * @inheritDoc
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        $err = ['code' => $throwable->getCode(), 'msg' => $throwable->getMessage(), 'data' => [], 'status' => false];
        if ($throwable instanceof ValidationException) {
            $err['msg'] = $throwable->validator->errors()->first();
            $keys = $throwable->validator->getMessageBag()->keys();
            $errors = $throwable->validator->errors();
            foreach ($keys as $key) {
                $err['data'][] = [
                    'parameter' => $key,
                    'errorInfo' => current($errors->get($key))
                ];
            }
        } elseif ($throwable instanceof BusinessException) {
            foreach ($throwable->errors as $key => $er) {
                $err['data'][] = [
                    'parameter' => $key,
                    'errorInfo' => $er
                ];
            }
        }
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200)->withBody(new SwooleStream(ToolsHelper::jsonEncode($err)));
    }

    /**
     * @inheritDoc
     */
    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException || $throwable instanceof BusinessException;
    }
}