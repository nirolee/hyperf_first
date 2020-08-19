<?php
declare(strict_types=1);

namespace App\Admin\Middleware;

use App\Admin\Annotation\IgnorePermissionValidateAnnotation;
use App\Admin\Constants\BusinessConst;
use App\Admin\Constants\ErrorCode;
use App\Admin\Exception\BusinessException;
use Hyperf\Contract\SessionInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 权限校验中间件
 * Class PermissionCheckMiddleware
 * @package App\Admin\Middleware
 */
class PermissionCheckMiddleware implements MiddlewareInterface
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
            $classes = AnnotationCollector::getClassesByAnnotation(IgnorePermissionValidateAnnotation::class);
            // 校验类注解是否忽略校验
            foreach ($classes as $classFilePath => $class) {
                if (strtolower($classFilePath) == $controllerName) {
                    $ignoreLoginValidate = true;
                    break;
                }
            }
            // 校验方法注解是否有忽略校验
            $methods = AnnotationCollector::getMethodsByAnnotation(IgnorePermissionValidateAnnotation::class);
            foreach ($methods as $method) {
                if (strtolower($method['method']) == $methodName && strtolower($method['class']) == $controllerName) {
                    $ignoreLoginValidate = true;
                    break;
                }
            }

            if (!$ignoreLoginValidate) {
                $permissionsList = $this->session->get(BusinessConst::ADMIN_PREMISSION_LIST_KEY);
                if (isset($permissionsList[$request->getUri()->getPath()])) {
                    // 权限验证通过
                    return $handler->handle($request);
                } else {
                    // 无权限操作
                    $acceptHeader = $request->getHeaderLine('X-Requested-With');
                    $jsonResponse = strtolower($acceptHeader) == 'xmlhttprequest' ? true : false;
                    if ($jsonResponse) {
                        $acceptHeader = $request->getHeaderLine('accept');
                        if (strpos(strtolower($acceptHeader), 'text/html') === false) {
                            // ajax text/json 输出
                            throw new BusinessException(ErrorCode::UNAUTHORIZED_ERROR);
                        } else {
                            // ajax text/html 输出
                            return $response->withHeader('Content-Type', 'text/html')->withStatus(200)->withBody(new SwooleStream('unauthorized'));
                        }
                    } else {
                        // 重定向输出
                        $response = $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class);
                        return $response->redirect('/unauthorized');
                    }
                }
            }
        }
        return $handler->handle($request);
    }
}