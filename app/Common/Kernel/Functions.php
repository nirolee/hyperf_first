<?php
declare(strict_types=1);

/**
 * describe: 减法运算
 * author: derick
 * date: 2020/1/13
 * @param int $scale 精度
 * @param mixed ...$operand 操作数
 * @return string
 */
function bcsubs(int $scale, ...$operand) : string {
    $result = strval(array_shift($operand));
    foreach ($operand as $o) {
        $result = bcsub($result, strval($o), $scale);
    }
    return $result;
}

/**
 * describe: 加法运算
 * author: derick
 * date: 2020/1/13
 * @param int $scale 精度
 * @param mixed ...$operand 操作数
 * @return string
 */
function bcadds(int $scale, ...$operand) : string {
    $result = strval(array_shift($operand));
    foreach ($operand as $o) {
        $result = bcadd($result, strval($o), $scale);
    }
    return $result;
}

/**
 * describe: 乘法运算
 * author: derick
 * date: 2020/1/13
 * @param int $scale 精度
 * @param mixed ...$operand 操作数
 * @return string
 */
function bcmuls(int $scale, ...$operand) : string {
    $result = strval(array_shift($operand));
    foreach ($operand as $o) {
        $result = bcmul($result, strval($o), $scale);
    }
    return $result;
}

/**
 * describe: 除法运算
 * author: derick
 * date: 2020/1/13
 * @param int $scale 精度
 * @param mixed ...$operand 操作数
 * @return string
 */
function bcdivs(int $scale, ...$operand) : string {
    $result = strval(array_shift($operand));
    foreach ($operand as $o) {
        $result = bcdiv($result, strval($o), $scale);
    }
    return $result;
}

/**
 * describe: 生成请求地址
 * author: derick
 * date: 2020/1/22
 * @param string $url 请求地址
 * @param array $args 请求附带参数
 * @return string
 */
function url(string $url, Array $args = []) : string {
    if ($args) {
        $url .= '?'.\Hyperf\Utils\Arr::query($args);
    }
    return $url;
}

/**
 * describe 生成伪静态路由地址
 * author derick
 * date 2020/4/18
 * @param string $url 接口地址
 * @param array $args 接口参数
 * @param string $suffix 请求后缀
 * @return string
 */
function seo_url(string $url, Array $args = [], string $suffix = 'shtml') : string {
    if ($args) {
        $args = '/'.implode('/', array_values($args));
    }
    return $url.$args.'.'.$suffix;
}

/**
 * describe: 获取静态资源地址
 * author: derick
 * date: 2020/1/22
 * @param string $path 文件地址
 * @return string 全地址
 */
function static_url(string $path) : string {
    return $path;
}

/**
 * describe 获取上传文件访问路径
 * author derick
 * date 2020/4/10
 * @param string $filename 文件名
 * @return string
 */
function get_uploadfile_url(string $filename) : string {
    $driver = config('file.default');
    $accessUrl = config('file.storage.'.$driver.'.accessUrl', '');
    return $accessUrl.$filename;
}