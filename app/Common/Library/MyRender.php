<?php
declare(strict_types=1);

/**
 * This file is copy from hyperf/view Render.php
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Common\Library;

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Task\Task;
use Hyperf\Task\TaskExecutor;
use Hyperf\Utils\Context;
use Hyperf\View\Engine\EngineInterface;
use Hyperf\View\Engine\SmartyEngine;
use Hyperf\View\Exception\EngineNotFindException;
use Hyperf\View\Mode;
use Hyperf\View\RenderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class MyRender implements RenderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $engine;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        $engine = $config->get('view.engine', SmartyEngine::class);
        if (!$container->has($engine)) {
            throw new EngineNotFindException("{$engine} engine is not found.");
        }
        $this->container = $container;
        $this->engine = $engine;
        $this->mode = $config->get('view.mode', Mode::TASK);
    }

    public function render(string $template, array $data = []): ResponseInterface
    {
        $config = $this->getConfigs();
        switch ($this->mode) {
            case Mode::SYNC:
                /** @var EngineInterface $engine */
                $engine = $this->container->get($this->engine);
                $result = $engine->render($template, $data, $config);
                break;
            case Mode::TASK:
            default:
                $executor = $this->container->get(TaskExecutor::class);
                $result = $executor->execute(new Task([$this->engine, 'render'], [$template, $data, $config]));
        }

        return $this->response()->withAddedHeader('content-type', 'text/html')->withBody(new SwooleStream($result));
    }

    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }

    public function getContents(string $template, array $data = []): string
    {
        $config = $this->getConfigs();
        switch ($this->mode) {
            case Mode::SYNC:
                /** @var EngineInterface $engine */
                $engine = $this->container->get($this->engine);
                $result = $engine->render($template, $data, $config);
                break;
            case Mode::TASK:
            default:
                $executor = $this->container->get(TaskExecutor::class);
                $result = $executor->execute(new Task([$this->engine, 'render'], [$template, $data, $config]));
                break;
        }
        return $result;
    }

    /**
     * describe 获取模板配置
     * author derick
     * date 2020/4/14
     * @return array
     */
    private function getConfigs() : array {
        $request = $this->container->get(RequestInterface::class);
        $serverPort = $request->server('server_port');
        $serverConfigs = config('server.servers', []);
        $configKey = 'view.config';
        foreach ($serverConfigs as $serverConfig) {
            if ($serverConfig['port'] == $serverPort) {
                $configKey .= '.' . $serverConfig['name'];
            }
        }
        return config($configKey, []);
    }
}
