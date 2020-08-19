<?php


namespace App\Admin\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpServer\Annotation\Controller;

/**
 * descript: admin应用Controller注解
 * Class AdminController
 * @package App\Admin\Annotation
 * @Annotation
 * @Target({"CLASS"})
 */
class AdminController extends Controller
{
    /**
     * @var string
     */
    public $server = ADMIN_SERVER;

    public function collectClass(string $className): void
    {
        AnnotationCollector::collectClass($className, Controller::class, $this);
    }

}