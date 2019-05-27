<?php
/**
 * 短信相关队列业务封装。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Services\Sms;

use Utils\YCore;
use Utils\YCache;
use Utils\YLog;

class Queue extends \Services\Sms\AbstractBase
{
    /**
     * 入短信队列
     * 
     * @param array $data
     * [
     *      'mobile'  => 手机号,
     *      'content' => 内容,
     *      'id'      => 日志id
     * ]
     */
    public static function push(array $data)
    {
        $redis = YCache::getRedisClient();
        $bool  = $redis->lPush(self::SMS_QUEUE_KEY, json_encode($data, JSON_UNESCAPED_UNICODE));
        if ($bool === false) {
            YLog::log($data, 'sms', 'push_queue_error');
            YCore::exception(STATUS_SERVER_ERROR, '短信发送失败');
        } else {
            YLog::log(['bool' => $bool, 'data' => $data], 'sms', 'push_queue_ok');
        }
    }
}