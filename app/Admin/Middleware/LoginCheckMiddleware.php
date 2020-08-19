<?php
declare(strict_types=1);

namespace App\Admin\Middleware;

use App\Admin\Annotation\IgnoreLoginValidateAnnotation;
use App\Admin\Constants\BusinessConst;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 登录校验拦截器
 * Class LoginCheckMiddleware
 * @package App\Admin\Middleware
 */
class LoginCheckMiddleware implements MiddlewareInterface
{

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @Inject()
     * @var SessionInterface
     */
    private SessionInterface $session;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * @var Dispatched $dispatched
         */
        $dispatched = $request->getAttribute(Dispatched::class);
        if ($dispatched->handler) {
            $response = Context::get(ResponseInterface::class);
            $controllerName = strtolower($dispatched->handler->callback[0]);
            $methodName = strtolower($dispatched->handler->callback[1]);
            $ignoreLoginValidate = false;
            $classes = AnnotationCollector::getClassesByAnnotation(IgnoreLoginValidateAnnotation::class);
            // 校验类注解是否忽略校验
            foreach ($classes as $classFilePath => $class) {
                if (strtolower($classFilePath) == $controllerName) {
                    $ignoreLoginValidate = true;
                    break;
                }
            }
            // 校验方法注解是否有忽略校验
            $methods = AnnotationCollector::getMethodsByAnnotation(IgnoreLoginValidateAnnotation::class);
            foreach ($methods as $method) {
                if (strtolower($method['method']) == $methodName && strtolower($method['class']) == $controllerName) {
                    $ignoreLoginValidate = true;
                    break;
                }
            }

            if (!$ignoreLoginValidate) {
                $admin = $this->session->get(BusinessConst::ADMIN_SESSION_KEY);
                if (empty($admin)) {
                    $response = $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class);
                    return $response->redirect('/login');
                }
                // 重新激活session有效时间
//            $this->session->set($this->session->getId(), $this->session->all());
            }
        }
        return $handler->handle($request);
    }
}