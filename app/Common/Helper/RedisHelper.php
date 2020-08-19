<?php
declare(strict_types=1);

namespace App\Common\Helper;

use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;
use Swoole\Coroutine;

class RedisHelper
{

    private $redis = null;

    public function __construct(string $pollName = 'default')
    {
        $this->redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get($pollName);
    }

    /**
     * describe: set 数据类型数据
     * author: derick
     * date: 2019/12/11
     * @param string $key 缓存key
     * @param mixed $value 缓存值. 使用数值替换boolean值.
     * @param int $ttl 过期时间
     * @return bool
     */
    public function set(string $key, $value, int $ttl = 0): bool
    {
        $key = $this->generateRedisKey($key);
        if (is_array($value)) {
            $value = ToolsHelper::jsonEncode($value);
        }
        if (is_bool($value)) {
            $value = intval($value);
        }
        return $this->redis->set($key, $value, $ttl);
    }

    /**
     * describe 加锁
     * author derick
     * date 2020/2/28
     * @param string $key 锁key
     * @param int $lockTime 锁有效时间(单位秒)
     * @param float $getLockPerTime 每次取锁间隔时间(单位秒)
     * @param int $waitTimeoutLockIfUnSuccess 如果加锁失败最大等待时间 (单位秒)
     * @return bool
     */
    public function lock(string $key, int $lockTime = 10, float $getLockPerTime = 0.05, int $waitTimeoutLockIfUnSuccess = 3): bool
    {
        $key = $this->generateRedisKey($key);
        $result = $this->get($key, '', false);
        if (is_numeric($result) && $result < time()) {
            // 因异常导致上次锁过期不能正常删除, 手动删除
            $this->delete($key, false);
            $result = null;
        }
        if (empty($result)) {
            // 无锁或者锁过期, 重新生成锁
            $continue = true;
            $begin = time();
            while ($continue) {
                $now = time();
                $value = time() + $lockTime;
                $lockSuccess = $this->redis->setnx($key, $value);
                if (!empty($lockSuccess) || ($this->get($key, '', false) < $now && $this->redis->getSet($key, $value) < $now)) {
                    $this->redis->expire($key, $lockTime);
                    $continue = false;
                } else {
                    // 获取锁失败, 等待下次尝试
                    Coroutine::sleep($getLockPerTime);
                }

                if ($waitTimeoutLockIfUnSuccess > 0 && $now - $begin >= $waitTimeoutLockIfUnSuccess) {
                    // 获取锁超时
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * describe: get 数据
     * author: derick
     * date: 2019/12/11
     * @param string $key 缓存key
     * @param mixed $defaultValue 非空时返回默认值
     * @param bool $encryptKey 是否已加密key
     * @return mixed
     */
    public function get(string $key, $defaultValue = '', bool $encryptKey = true)
    {
        $key = $encryptKey ? $this->generateRedisKey($key) : $key;
        $val = $this->redis->get($key);
        if (empty($val) && !is_numeric($val)) {
            return $defaultValue;
        }
        $decodeValue = ToolsHelper::jsonDecode($val);
        if (is_null($decodeValue)) {
            return empty($val) ? $defaultValue : $val;
        }
        return $decodeValue;
    }

    /**
     * describe: 自增
     * author: derick
     * date: 2019/12/30
     * @param string $key redis key
     * @param int $incrBy 自增值
     * @param int $ttl 过期时间
     * @return int
     */
    public function incr(string $key, int $incrBy = 1, int $ttl = 0): int
    {
        $key = $this->generateRedisKey($key);
        $val = $this->redis->incrBy($key, $incrBy);
        if ($ttl > 0) {
            $this->redis->expire($key, $ttl);
        }
        return intval($val);
    }

    /**
     * describe: 自减
     * author: derick
     * date: 2019/12/30
     * @param string $key redis key
     * @param int $decrBy 自减值
     * @param int $ttl 过期时间
     * @return int
     */
    public function decr(string $key, int $decrBy = 1, int $ttl = 0): int
    {
        $key = $this->generateRedisKey($key);
        $val = $this->redis->decrBy($key, $decrBy);
        if ($ttl > 0) {
            $this->redis->expire($key, $ttl);
        }
        return $val;
    }

    /**
     * describe 解锁
     * author derick
     * date 2020/2/28
     * @param string $key 锁key
     * @return int
     */
    public function unlock(string $key): int
    {
        if ($this->redis->ttl($key)) {
            return $this->delete($key);
        }
        return 1;
    }

    /**
     * describe 删除缓存
     * author derick
     * date 2020/2/28
     * @param string $key 缓存key
     * @param bool $encryptKey 是否需要加密key
     * @return int
     */
    public function delete(string $key, bool $encryptKey = true): int
    {
        return $this->redis->del($encryptKey ? $this->generateRedisKey($key) : $key);
    }

    /**
     * describe 入队(LIST队列)
     * author 张旭之
     * date 2020/5/20
     * @param string $key
     * @param $value
     * @param int $ttl 过期时间(秒)
     * @return bool
     */
    public function enListQueue(string $key, $value, int $ttl = 0) : bool {
        $key = $this->generateRedisKey($key);
        $value = $this->redis->rPush($key, $value);
        if ($ttl > 0) {
            $this->redis->expire($key, $ttl);
        }
        return $value;
    }

    /**
     * describe 出队(LIST队列)
     * author 张旭之
     * date 2020/5/20
     * @param string $key
     * @return bool|mixed
     */
    public function deListQueue(string $key) {
        $key = $this->generateRedisKey($key);
        return $this->redis->lPop($key);
    }

    /**
     * describe 获取队列长度(LIST队列)
     * author 张旭之
     * date 2020/5/20
     * @param string $key
     * @return int
     */
    public function getListQueueLength(string $key) : int {
        $key = $this->generateRedisKey($key);
        return $this->redis->lLen($key);
    }

    /**
     * describe 入队(SET队列)
     * author 张旭之
     * date 2020/5/20
     * @param string $key
     * @param $value
     * @param int|null $ttl 过期时间(秒)
     * @return bool
     */
    public function enSetQueue(string $key, $value, int $ttl = null) : bool {
        $key = $this->generateRedisKey($key);
        $value = $this->redis->sAdd($key, $value);
        if ($ttl > 0) {
            $this->redis->expire($key, $ttl);
        }
        return $value;
    }

    /**
     * describe 出队(SET队列)
     * author 张旭之
     * date 2020/5/20
     * @param string $key
     * @return mixed
     */
    public function deSetQueue(string $key) {
        $key = $this->generateRedisKey($key);
        return $this->redis->sPop($key);
    }

    /**
     * describe 获取队列长度(SET队列)
     * author 张旭之
     * date 2020/5/20
     * @param string $key
     * @return int
     */
    public function getSetQueueLength(string $key) : int {
        $key = $this->generateRedisKey($key);
        return $this->redis->sCard($key);
    }

    /**
     * describe: 封装redis key
     * author: derick
     * date: 2019/12/11
     * @param string $key
     * @return string
     */
    private function generateRedisKey(string $key) : string
    {
        return env('REDIS_PREFIX') . ToolsHelper::get16Md5($key);
    }
}