<?php
/**
 * 监控生产者。
 * @author fingerQin
 * @date 2019-06-12
 */

namespace Services\Monitor;

use Utils\YCore;
use Utils\YCache;
use Models\Monitor;
use finger\Validator;

class Producer extends \Services\Monitor\AbstractBase
{
    /**
     * 推送系统事件消息。
     *
     * @param  array  $mssage  事件消息。
     *
     * @return void
     */
    public static function push(array $message)
    {
        // [1]
        if (empty($message)) {
            YCore::exception(STATUS_SERVER_ERROR, '消息内容不能为空');
        }
        if (!isset($message['code'])) {
            YCore::exception(STATUS_SERVER_ERROR, '监控位置 CODE 必须设置');
        }
        $code = strtolower($message['code']);
        if (!in_array($code, Monitor::$codeDict)) {
            YCore::exception(STATUS_SERVER_ERROR, '监控位置 CODE 错误');
        }
        // [2]
        $code = ucfirst($code);
        self::{"check{$code}Monitor"}($message);
        // [3] 写入 Redis 队列。
        $message['serial_no'] = self::serialNo();
        $redis  = YCache::getRedisClient();
        $status = $redis->lPush(self::MONITOR_QUEUE_KEY, json_encode($message, JSON_UNESCAPED_UNICODE));
        if ($status === false) {
            YCore::log($message, 'monitor', 'queue-error');
        }
    }

    /**
     * 返回监控流水序列号。
     *
     * @return string
     */
    protected static function serialNo()
    {
        $date   = date('YmdHi', TIMESTAMP);
        $key    = "monitor-{$date}";
        $redis  = YCache::getRedisClient();
        $intVal = $redis->incr($key);
        if ($intVal == 1) {
            $redis->expire($key, 120);
        }
        return $date . str_pad($intVal, 6, 0, STR_PAD_LEFT);
    }

    /**
     * 异常访问特殊 appid 应用。
     *
     * @param  array  $data  事件内容。
     * @return void
     *
     * -- eg:start --
     * $data = [
     *     'code'     => 'unauthorized',
     *     'ip'       => 'IP 地址',
     *     'datetime' => '注册时间',
     * ];
     * -- eg:end --
     */
    protected static function checkUnauthorizedMonitor($data)
    {
        $rules = [
            'code'     => 'CODE|require',
            'ip'       => 'IP 地址|require|ip',
            'datetime' => '触发时间|require|datetime',
        ];
        Validator::valido($data, $rules);
    }
}