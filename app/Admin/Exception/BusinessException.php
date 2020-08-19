<?php
declare(strict_types=1);

namespace App\Admin\Exception;
use App\Admin\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;

/**
 * 自定义业务异常类
 * Class BusinessException
 * @package App\Admin\Exception
 */
class BusinessException extends ServerException
{

    public $errors = [];

    /**
     * BusinessException constructor.
     * @param int $code 接口返回状态码
     * @param array $replace 接口返回消息模板中的变量
     * @param string $errorKey 错误关联字段
     * @param string|null $message 自定义接口返回消息, 如果定义了消息, 则此优先级最高
     * @param Throwable|null $previous
     */
    public function __construct(int $code = 0, Array $replace = [], string $errorKey = '', string $message = null, Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = ErrorCode::getMessage($code);
            $message = trans($message, $replace);
        }

        if ($errorKey) {
            $this->errors[$errorKey] = $message;
        }
        parent::__construct($message, $code, $previous);
    }
}