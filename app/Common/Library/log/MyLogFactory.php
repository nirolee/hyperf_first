<?php


namespace App\Common\Library\log;


use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;

/**
 * 日志工厂类
 * Class MyLogFactory
 * @package App\Common\Library\log
 */
class MyLogFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return $this->get();
    }

    public function get(string $name = 'app') {
        $group = config('log_engine', 'file');
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name, $group);
    }
}