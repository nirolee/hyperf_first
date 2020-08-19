<?php
declare(strict_types=1);

namespace App\Common\Helper;

use App\Common\Library\log\MyLog;
use App\Common\Library\log\MyLogFactory;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Codec\Json;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

class ToolsHelper
{

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
            self::$instance->filesystem = ApplicationContext::getContainer()->get(FilesystemFactory::class)->get(config('file.default', 'local'));
        }
        return self::$instance;
    }

    /**
     * describe: 获取16位长度md5值
     * author: derick
     * date: 2019/12/11
     * @param string $str 加密串
     * @return string
     */
    public static function get16Md5(string $str): string
    {
        return substr(md5($str), 8, 16);
    }

    /**
     * describe 10进制转36进制
     * author derick
     * date 2020/4/20
     * @param int $dec 十进制数
     * @return string
     */
    public static function decHexadecimal(int $dec): string
    {
        return base_convert($dec, 10, 36);
    }

    /**
     * describe 36进制转10进制
     * author derick
     * date 2020/4/20
     * @param string $hex 36进制字符串
     * @return int
     */
    public static function hexDecadecimal(string $hex): int
    {
        return intval(base_convert($hex, 36, 10));
    }

    /**
     * describe 按照指定key将数组重新分组
     * author derick
     * date 2020/4/23
     * @param array $array 源数组
     * @param string $key 指定key
     * @param bool $forcedReturnDimensionalArray 是否强制返回二维数组模式, 如果此参数设置为false, 当数组只有一个元素时, 会只返回一位数组
     * @return array
     */
    public static function resetArrayKey(Array $array, string $key, bool $forcedReturnDimensionalArray = false): array
    {
        $data = array();
        if (!self::isDimensionalArray($array)) {
            return $array;
        }
        foreach ($array as $a) {
            if (isset ($a [$key])) {
                if (isset ($data [$a [$key]])) {
                    if (self::isDimensionalArray($data [$a [$key]])) {
                        $tmp = $data [$a [$key]];
                    } else {
                        $tmp [] = $data [$a [$key]];
                    }
                    $tmp [] = $a;
                    $data [$a [$key]] = $tmp;
                    unset ($tmp);
                } else {
                    $data [$a [$key]] = $forcedReturnDimensionalArray ? array(
                        $a
                    ) : $a;
                }
            }
        }
        return $data;
    }

    /**
     * describe: 从数组集合中提取某个字段
     * author: derick
     * date: 2019/12/19
     * @param $array 数组源
     * @param $column  提取字段
     * @return array
     */
    public static function extractColumnFromArray($array, $column) : array {
        if (empty($array)) {
            return array();
        }
        $_array = is_object($array) ? $array->toArray() : $array;
        return Arr::pluck($_array, $column);
    }

    /**
     * describe 判断一个数组是否是一个二维(多维)数组
     * author derick
     * date 2020/4/23
     * @param array $array
     * @return bool
     */
    public static function isDimensionalArray(Array $array): bool
    {
        if (count($array) == count($array, 1)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * describe 生成uuid
     * author derick
     * date 2020/4/10
     * @param int $length
     * @param string $hyphen
     * @return string
     */
    public static function createGuid($length = 3, $hyphen = '-')
    {
        $charid = strtoupper(md5(uniqid(strval(mt_rand()), true)));
        $uuid = '';
        for ($i = 0; $i <= $length; $i++) {
            if ($i == $length) {
                $uuid .= substr($charid, $i, 4);
                continue;
            }
            $uuid .= substr($charid, $i, 4) . $hyphen;
        }
        return $uuid;
    }

    /**
     * describe 上传文件
     * author derick
     * date 2020/4/10
     * @param UploadedFile $file 文件对象
     * @param String $fileName 文件名
     * @return string 文件路径
     * @throws FileExistsException
     */
    public static function uploadTempFile(UploadedFile $file, string $fileName = ''): string
    {
        $fileName = $fileName ? $fileName : self::get16Md5(self::createGuid() . $file->getFilename()) . '.' . $file->getExtension();
        $fileName = date('Ymd') . '/' . $fileName;
        self::getInstance()->filesystem->write($fileName, file_get_contents($file->getRealPath()));
        return $fileName;
    }

    /**
     * describe 下载远程文件
     * author derick
     * date 2020/4/24
     * @param string $url 远程文件地址
     * @param string $fileName 新文件名
     * @return string
     * @throws FileExistsException
     */
    public static function uploadRemoteFile(string $url, string $fileName = ''): string
    {
        if (empty($url)) {
            return '';
        }
        $fileInfo = pathinfo($url);
        $ext = $fileInfo['extension'] ?? '';
        if (empty($ext)) {
            return '';
        }
        $curl = ApplicationContext::getContainer()->get(MyCurlHelper::class);
        $content = $curl->get($url, [], false);
        $fileName = $fileName ? $fileName : self::get16Md5(self::createGuid() . $fileInfo['filename']) . '.' . $ext;
        $fileName = date('Ymd') . '/' . $fileName;
        self::getInstance()->filesystem->write($fileName, $content);
        return $fileName;
    }

    /**
     * describe 下载文件
     * author derick
     * date 2020/4/15
     * @param string $file 文件路径
     * @return bool|false|mixed|string
     */
    public static function downloadFile(string $file)
    {
        try {
            return self::getInstance()->filesystem->read($file);
        } catch (\League\Flysystem\FileNotFoundException $exception) {
            return '';
        }
    }

    /**
     * describe 保存文件至本地磁盘
     * author derick
     * date 2020/4/15
     * @param string $fileContent 文件内容
     * @param string $localDiskPath 磁盘路径
     * @return bool
     * @throws FileExistsException
     */
    public static function saveFileToDisk(string $fileContent, string $localDiskPath)
    {
        if (empty($fileContent)) {
            return false;
        }
        $localFilesystem = ApplicationContext::getContainer()->get(FilesystemFactory::class)->get('local');
        if ($localFilesystem) {
            return $localFilesystem->write($localDiskPath, $fileContent);
        }
        return false;
    }

    /**
     * 创建深层目录
     * @param string $dir 路径
     * @param int $mode 权限模式
     * @return bool
     */
    public static function mkdirDeep(string $dir, int $mode = 0766): bool
    {
        if ($dir == '') {
            return false;
        } elseif (is_dir($dir) && @chmod($dir, $mode)) {
            return true;
        } elseif (@mkdir($dir, $mode, true)) {//第三个参数为true即可以创建多级目录
            return true;
        }

        return false;
    }

    /**
     * 遍历路径获取文件树
     * @param string $path 路径
     * @param string $type 获取类型:all-所有,dir-仅目录,file-仅文件
     * @param bool $recursive 是否递归
     * @return array
     */
    public static function getFileTree(string $path, string $type = 'all', bool $recursive = true): array
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $tree = [];
        // '{.,*}*' 相当于 '.*'(搜索.开头的隐藏文件)和'*'(搜索正常文件)
        foreach (glob($path . '/{.,*}*', GLOB_BRACE) as $single) {
            if (is_dir($single)) {
                $file = str_replace($path . '/', '', $single);
                if ($file == '.' || $file == '..') {
                    continue;
                }

                if ($type != 'file') {
                    array_push($tree, $single);
                }

                if ($recursive) {
                    $tree = array_merge($tree, self::getFileTree($single, $type, $recursive));
                }
            } elseif ($type != 'dir') {
                array_push($tree, $single);
            }
        }

        return $tree;
    }

    /**
     * 获取目录大小,单位[字节]
     * @param string $path
     * @return int
     */
    public static function getDirSize(string $path): int
    {
        $size = 0;
        if ($path == '' || !is_dir($path)) {
            return $size;
        }

        $dh = @opendir($path); //比dir($path)快
        while (false != ($file = @readdir($dh))) {
            if ($file != '.' and $file != '..') {
                $fielpath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($fielpath)) {
                    $size += self::getDirSize($fielpath);
                } else {
                    $size += filesize($fielpath);
                }
            }
        }
        @closedir($dh);
        return $size;
    }

    /**
     * 拷贝目录
     * @param string $from 源目录
     * @param string $dest 目标目录
     * @param bool $cover 是否覆盖已存在的文件
     * @return bool
     */
    public static function copyDir(string $from, string $dest, bool $cover = false): bool
    {
        if (!file_exists($dest) && !@mkdir($dest, 0766, true)) {
            return false;
        }

        $dh = @opendir($from);
        while (false !== ($fileName = @readdir($dh))) {
            if (($fileName != ".") && ($fileName != "..")) {
                $newFile = "$dest/$fileName";
                if (!is_dir("$from/$fileName")) {
                    if (file_exists($newFile) && !$cover) {
                        continue;
                    } elseif (!copy("$from/$fileName", $newFile)) {
                        return false;
                    }
                } else {
                    self::copyDir("$from/$fileName", $newFile, $cover);
                }
            }
        }
        @closedir($dh);

        return true;
    }

    /**
     * 批量改变目录模式(包括子目录和所属文件)
     * @param string $path 路径
     * @param int $filemode 文件模式
     * @param int $dirmode 目录模式
     */
    public static function chmodBatch(string $path, int $filemode = 0766, int $dirmode = 0766): void
    {
        if ($path == '') {
            return;
        }

        if (is_dir($path)) {
            if (!@chmod($path, $dirmode)) {
                return;
            }
            $dh = @opendir($path);
            while (($file = @readdir($dh)) !== false) {
                if ($file != '.' && $file != '..') {
                    $fullpath = $path . '/' . $file;
                    self::chmodBatch($fullpath, $filemode, $dirmode);
                }
            }
            @closedir($dh);
        } elseif (!is_link($path)) {
            @chmod($path, $filemode);
        }
    }

    /**
     * 删除目录(目录下所有文件,包括本目录)
     * @param string $path
     * @return bool
     */
    public static function delDir(string $path): bool
    {
        if (is_dir($path) && $dh = @opendir($path)) {
            while (false != ($file = @readdir($dh))) {
                if ($file != '.' && $file != '..') {
                    $fielpath = $path . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($fielpath)) {
                        self::delDir($fielpath);
                    } else {
                        @unlink($fielpath);
                    }
                }
            }
            @closedir($dh);
            return @rmdir($path);
        }
        return false;
    }

    /**
     * 清空目录(删除目录下所有文件,仅保留当前目录)
     * @param string $path
     * @return bool
     */
    public static function clearDir(string $path): bool
    {
        if (empty($path) || !is_dir($path)) {
            return false;
        }

        $dirs = [];
        $dir = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $single => $file) {
            $fpath = $file->getRealPath();
            if ($file->isDir()) {
                array_push($dirs, $fpath);
            } else {
                //先删除文件
                @unlink($fpath);
            }
        }

        //再删除目录
        rsort($dirs);
        foreach ($dirs as $dir) {
            @rmdir($dir);
        }

        unset($objects, $object, $dirs);
        return true;
    }

    /**
     * 格式化路径字符串(路径后面加/)
     * @param string $dir
     * @return string
     */
    public static function formatDir(string $dir): string
    {
        if ($dir == '') {
            return '';
        }

        $order = [
            '\\',
            "'",
            '#',
            '=',
            '`',
            '$',
            '%',
            '&',
            ';',
            '|'
        ];
        $replace = [
            '/',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];

        $dir = str_replace($order, $replace, $dir);
        return rtrim(preg_replace(RegularHelper::$patternDoubleSlash, '/', $dir), ' /　') . '/';
    }

    /**
     * describe: 写日志
     * author 张旭之
     * date 2020/5/19
     * @param string $message 日志内容
     * @param string $logType 日志类型
     * @param array $context 其他参数
     * @return bool
     */
    public static function log(string $message, string $logType = 'app', Array $context = []) : bool {
        $logger = ApplicationContext::getContainer()->get(MyLogFactory::class)->get($logType);
        if (config('log_switch')) {
            $type = $context['type'] ?? 'info';
            switch (strtolower($type)) {
                case 'error':
                    $logger->error($message, $context);
                    break;
                default:
                    $logger->info($message, $context);
                    break;
            }

            // 是否发送通知
            if (isset($context['notice']) && $context['notice']) {

            }
        }
        return true;
    }

    /**
     * describe: json encode
     * author 张旭之
     * date 2020/5/19
     * @param $data 编码数据
     * @param int $options
     * @return string
     */
    public static function jsonEncode($data, int $options = JSON_UNESCAPED_UNICODE) : string {
        return Json::encode($data ,$options);
    }

    /**
     * describe: json decode
     * author 张旭之
     * date 2020/5/19
     * @param string $json json字符串
     * @param bool $assoc
     * @return mixed
     */
    public static function jsonDecode(string $json, $assoc = true) {
        return Json::decode($json, $assoc);
    }

    /**
     * describe: 获取距离今天24点相差的秒数
     * author: derick
     * date: 2020/6/24
     * @return int
     */
    public static function getTodayEndSecond(): int {
        return strtotime(date('Y-m-d')) + 86399 - time();
    }
}