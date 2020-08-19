<?php
declare(strict_types=1);

namespace App\Common\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * describe: redis key集合配置类
 * Class RedisKey
 * @package App\Constants
 * @Constants()
 */
class RedisKey extends AbstractConstants
{
    /**
     * 用户基础信息key
     */
    CONST USER_INFO = 'USER_INFO_';

    /**
     * describe 获取redis key
     * author derick
     * date 2020/6/21
     * @param string $redisKey key
     * @param array $replace 占位符
     * @return string
     */
    public static function getRedisKey(string $redisKey, Array $replace = []) : string {
        $search = $replace = [];
        foreach ($replace as $rkey => $rvalue) {
            $search[] = '%'.strtoupper($rkey).'%';
            $replace[] = $rvalue;
        }
        return str_replace('', '', $redisKey);
    }
}