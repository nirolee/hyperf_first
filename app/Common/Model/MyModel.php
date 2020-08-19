<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model as BaseModel;

abstract class MyModel extends BaseModel
{

    /**
     * describe 判断当前对象是否为空
     * author derick
     * date 2020/3/13
     * @return bool
     */
    public function isEmpty() : bool {
        return empty($this->toArray());
    }

    /**
     * describe: 批量写入
     * author: 张旭之
     * date: 2020/6/18
     * @param array $arr
     * @return bool
     */
    public function batchSave(Array $arr) : bool {
        if (empty($arr)) {
            return true;
        }
        $this->newQuery()->insert($arr);
        return true;
    }
}