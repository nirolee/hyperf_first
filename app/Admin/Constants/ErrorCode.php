<?php
declare(strict_types=1);

namespace App\Admin\Constants;


use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * Class ErrorCode
 * @package App\Admin\Constants
 * @Constants()
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("message.success")
     */
    CONST REQUEST_SUCCESS = 0;
    /**
     * @Message("message.form_validate_fail")
     */
    CONST PARAMETER_VALIDATION_ERROR = 99;
    /**
     * @Message("message.api_return_error")
     */
    CONST API_RETURN_ERROR = 98;
    /**
     * @Message("message.api_network_error")
     */
    CONST API_NETWORK_ERROR = 97;
    /**
     * @Message("message.api_not_found")
     */
    CONST API_NOT_FOUND = 96;
    /**
     * @Message("message.usu_error_tips")
     */
    CONST USU_ERROR = 95;
    /**
     * @Message("message.json_parse_error")
     */
    CONST JSON_PARSE_ERROR = 94;
}