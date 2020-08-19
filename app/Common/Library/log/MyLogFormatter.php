<?php


namespace App\Common\Library\log;


use Hyperf\Utils\Coroutine;
use Monolog\Formatter\NormalizerFormatter;

/**
 * 日志内容格式化类
 * Class MyLogFormatter
 * @package App\Common\Library\log
 */
class MyLogFormatter extends NormalizerFormatter
{
    public function format(array $record) {
        $data = array_merge([
            'level' => $record['level_name'],
            'type' => $record['channel'],
            'message' => $record['message'],
            'create_time' => date('Y-m-d H:i:s'),
            'coroutine_id' => Coroutine::id(),
        ], $record['context'] ?? []);
        return $this->toJson($this->normalize($data), true)."\n\n";
    }

    public function formatBatch(array $records)
    {
        return $this->format($records);
    }
}