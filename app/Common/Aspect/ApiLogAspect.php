<?php
declare(strict_types=1);

namespace App\Common\Aspect;

use App\Common\Helper\ToolsHelper;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * Class ApiLogAspect
 * @package App\Common\Aspect
 * @Aspect()
 */
class ApiLogAspect extends AbstractAspect
{

    public $classes = [
        'App\Common\Helper\MyCurlHelper::_request',
    ];

    /**
     * @inheritDoc
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $args = $proceedingJoinPoint->getArguments();
        try {
            $processData = $proceedingJoinPoint->process();
        } catch (\Exception $e) {
            $processData = $e->getMessage();
        }
        ToolsHelper::log('', 'curl', [
            'request_data' => ['params' => $args],
            'response_data' => $processData,
        ]);
        return $processData;
    }
}