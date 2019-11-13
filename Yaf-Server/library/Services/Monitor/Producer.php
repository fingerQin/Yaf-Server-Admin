<?php
/**
 * 监控生产者。
 * @author fingerQin
 * @date 2019-06-12
 */

namespace Services\Monitor;

use finger\Utils\YCore;
use finger\Utils\YCache;
use Models\Monitor;
use finger\Validator;

class Producer extends \Services\Monitor\AbstractBase
{
    /**
     * 监控上报数据推送。
     *
     * @param  array  $mssage     上报数据。
     * @param  int    $frequency  上报频繁。单位（分）。0 代表不限制。其它大于 0 的值代表多少分钟内不上报相关的数据。
     *
     * @return void
     */
    public static function report(array $message, $frequency = 0)
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
        self::checkFrequency($message['code'], $frequency);
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
     * 验证监控频率。
     *
     * @param  string  $code       监控位置编码。
     * @param  int     $frequency  上报频率。
     *
     * @return bool true 可以上报、false 超过限定频率。
     */
    protected static function checkFrequency($code, $frequency)
    {
        if ($frequency <= 0) {
            return true;
        } else {
            $frequencyKey = "monitor-frequency-{$code}";
            $redis        = YCache::getRedisClient();
            $status       = $redis->set($frequencyKey, 1, ['NX', 'EX' => $frequency * 60]);
            return $status ? true : false;
        }
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