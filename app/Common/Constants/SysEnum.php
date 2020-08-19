<?php


namespace App\Common\Constants;


use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 系统枚举配置类
 * Class SysEnum
 * @package App\Common\Constants
 * @Constants()
 */
class SysEnum extends AbstractConstants
{
    /**
     * 默认队列
     */
    CONST DEFAULT_QUEUE = 'default_queue';
    /**
     * 错误告警队列
     */
    CONST ERROR_NOTICE_QUEUE = 'error_notice_queue';
}