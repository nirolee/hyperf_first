<?php

declare(strict_types=1);

namespace App\Common\Listener;

use App\Common\Helper\ValidateHelper;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * 自定义验证器初始化类
 * @Listener
 */
class ValidatorFactoryResolvedListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class
        ];
    }

    public function process(object $event)
    {
        /**  @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;
        // 注册自定义mobile验证器
        $validatorFactory->extend('mobile', function ($attribute, $value, $parameters, $validator) {
            return ValidateHelper::isMobilecn($value);
        });

        // 注册mysql字段验证器
        $rules = [
            [
                'ruleName' => 'bigint',
                'min' => '-9223372036854775808',
                'max' => '9223372036854775807'
            ],
            [
                'ruleName' => 'unsignedbigint',
                'min' => '0',
                'max' => '18446744073709551615'
            ],
            [
                'ruleName' => 'int',
                'min' => '-2147483648',
                'max' => '2147483647'
            ],
            [
                'ruleName' => 'unsignedint',
                'min' => '0',
                'max' => '4294967295'
            ],
            [
                'ruleName' => 'mediumint',
                'min' => '-8388608',
                'max' => '8388607'
            ],
            [
                'ruleName' => 'unsignedmediumint',
                'min' => '0',
                'max' => '16777215'
            ],
            [
                'ruleName' => 'smallint',
                'min' => '-32768',
                'max' => '32767'
            ],
            [
                'ruleName' => 'unsignedsmallint',
                'min' => '0',
                'max' => '65535'
            ],
            [
                'ruleName' => 'tinyint',
                'min' => '-128',
                'max' => '127'
            ],
            [
                'ruleName' => 'unsignedtinyint',
                'min' => '0',
                'max' => '255'
            ],
        ];
        foreach ($rules as $r) {
            $validatorFactory->extend($r['ruleName'], function ($attribute, $value, $parameters, $validator) use ($r){
                $value = intval($value);
                if ($value >= $r['min'] && $value <= $r['max']) {
                    return true;
                }
                return false;
            });
            // 自定义错误提示
//            $factory->replacer('bigint', function ($message, $attribute, $rule, $parameters) {
//                return str_replace(':bigint', $attribute, $message);
//            });
        }
    }
}
