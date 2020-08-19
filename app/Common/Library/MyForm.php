<?php
declare(strict_types=1);

namespace App\Common\Library;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Request\FormRequest;

/**
 * describe:
 * Class MyForm
 * @package App\Library
 */
abstract class MyForm extends FormRequest
{

    /**
     * @Inject()
     * @var ValidatorFactoryInterface
     */
    protected ValidatorFactoryInterface $validatorFactory;

    /**
     * describe 校验表单
     * author derick
     * date 2019/12/8
     * @param String $scene 校验场景
     * @param array $checkData 校验数据
     * @return array
     */
    public function doCheck(string $scene = '', Array $checkData = array()) : array {
        $checkData = empty($checkData) ? $this->all() : $checkData;
        $_rules = Arr::get($this->rules(), $scene, $this->rules());
        $rules = [];
        // 如果 scene()方法没有指定验证的字段, 则默认读取所有rules()方法里配置了规则的字段
        foreach (Arr::get($this->scene(), $scene, []) as $attr) {
            if (isset($_rules[$attr])) {
                $rules[$attr] = $_rules[$attr];
            }
        }
        $rules = $rules ? $rules : $_rules;
        return $this->validatorFactory->make($checkData, $rules, $this->message(), $this->attributes())->validate();
    }

    public function attributes(): array
    {
        return parent::attributes();
    }

    abstract protected function rules() : array;

    abstract protected function message() : array;

    abstract protected function scene() : array;
}