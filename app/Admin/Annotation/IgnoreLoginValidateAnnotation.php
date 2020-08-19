<?php
declare(strict_types=1);

namespace App\Admin\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 忽略登录校验注解
 * Class IgnoreLoginValidateAnnotation
 * @package App\Admin\Annotation
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class IgnoreLoginValidateAnnotation extends AbstractAnnotation
{

}