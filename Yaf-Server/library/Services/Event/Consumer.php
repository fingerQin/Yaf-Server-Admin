<?php
/**
 * 消费者。
 * @author fingerQin
 * @date 2018-09-06
 */

namespace Services\Event;

use Utils\YCache;
use Utils\YLog;
use Utils\YCore;

class Consumer extends \Services\Event\AbstractBase
{
    /**
     * 事件消费者分发器。
     * 
     * -- 该方法主要是将事件队列中的队列拆分到多个单独的队列当中。
     * -- 单独的队列有单独的进程消费，互相的处理结果不受影响。
     *
     * @return void
     */
    public static function dispatcher()
    {
        // [1]
        $redis            = YCache::getRedisClient();
        $eventQueueKey    = self::EVENT_QUEUE_KEY;
        $eventQueueIngKey = self::EVENT_QUEUE_KEY . '-ing';

        // [2] 无限循环处理消息队列的数据。
        // [2.1] 将当前正在处理的事件归恢复到事件池中。主要是预防进程重启导致正在处理的事件未正确处理。
        while ($redis->rPopLPush($eventQueueIngKey, $eventQueueKey)) {
            // 因为仅仅是利用 while 语句的循环特性,所以这里不需要实际业务代码。
        }

        // [2.2] 无限循环让进程一直处于常驻状态。
        try {
            while(true) {
                $strEventVal = $redis->bRPopLPush($eventQueueKey, $eventQueueIngKey, 3);
                if ($strEventVal) {
                    $arrEventVal = json_decode($strEventVal, true);
                    $subEventkey = self::EVENT_PREFIX . '_' . $arrEventVal['code']; // 子事件队列 KEY。
                    $redis->lPush($subEventkey, $strEventVal); // 将正在处理的事件放入对应子事件池队列中。
                    $redis->lRem($eventQueueIngKey, $strEventVal, 1); // 从正在处理的队列中移除这个值。
                    YLog::log($arrEventVal, 'event', 'dispatcher-success');
                } else {
                    YCache::ping();
                }
            }
        } catch (\Exception $e) {
            YLog::log($e->getMessage(), 'event', 'dispatcher-error');
            $datetime = date('Y-m-d H:i:s', time());
            exit("Error Time:{$datetime}\nAn exception occurred in the event program!\n" . $e->getMessage() . "\n");
        }
    }
}