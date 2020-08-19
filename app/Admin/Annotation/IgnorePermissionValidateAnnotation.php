<?php
declare(strict_types=1);

namespace App\Admin\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 忽略权限校验注解
 * Class IgnoreLoginValidateAnnotation
 * @package App\Admin\Annotation
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class IgnorePermissionValidateAnnotation extends AbstractAnnotation
{

}