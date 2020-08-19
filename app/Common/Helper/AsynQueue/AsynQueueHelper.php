<?php
declare(strict_types=1);

namespace App\Common\Helper\AsynQueue;

use App\Common\Constants\SysEnum;
use App\Common\Helper\ToolsHelper;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Exception\InvalidDriverException;
use Hyperf\AsyncQueue\Job;
use Hyperf\Di\Annotation\Inject;

/**
 * redis 异步队列帮助类
 * Class AsynQueueHelper
 * @package App\Common\Helper\AsynQueue
 */
class AsynQueueHelper
{
    /**
     * @Inject()
     * @var DriverFactory
     */
    private DriverFactory $driverFactory;

    /**
     * describe: 入队
     * author: 张旭之
     * date: 2020/8/7
     * @param Job $job 入队元素对象
     * @param int $delay 延迟处理时间(秒)
     * @param string $queueName 队列名称
     * @return bool
     */
    public function push(Job $job, int $delay = 0, string $queueName = SysEnum::DEFAULT_QUEUE): bool {
        try {
            $driver = $this->driverFactory->get($queueName);
            return $driver->push($job, $delay);
        } catch (InvalidDriverException $exception) {
            ToolsHelper::log($exception->getMessage());
            return false;
        }
    }
}