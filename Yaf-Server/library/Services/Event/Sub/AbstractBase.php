<?php
/**
 * 子事件基类。
 * @author fingerQin
 * @date 2018-09-13
 */

namespace Services\Event\Sub;

use Utils\YLog;
use Utils\YCache;
use Utils\YCore;
use Models\Event;

abstract class AbstractBase extends \Services\Event\AbstractBase
{
    /**
     * 子事件启动入口。
     * 
     * -- 注意
     * -- 1) 该方法只能由 PHP CLI 模式调用执行。
     * -- 2) 执行该方法之前不要有任何的 Redis 连接操作。
     * -- 3) 如果是单进程运行该方法可以忽略第 2 点。如果是多进程运行，必须保证第二点。因为，Redis 阻塞只能由多个 Redis 连接操作。
     *
     * @param  string  $code        事件 CODE。
     * @param  int     $retryCount  失败重试次数。只有服务器报错才会重试。如果是提示卡券不存在这种错误则不会。
     * @param  int     $interval    重试间隔时间。单位(秒)。
     *
     * @return void
     */
    public static function launch($code, $retryCount = 0, $interval = 0)
    {
        // [1]
        $redis            = YCache::getRedisClient();
        $eventQueueKey    = self::EVENT_PREFIX . '_' . $code;
        $eventQueueIngKey = $eventQueueKey . '-ing';

        // [2] 无限循环处理消息队列的数据。
        // [2.1] 将当前正在处理的事件归恢复到事件池中。主要是预防进程重启导致正在处理的事件未正确处理。
        while ($redis->rPopLPush($eventQueueIngKey, $eventQueueKey)) {
            // 因为仅仅是利用 while 语句的循环特性,所以这里不需要实际业务代码。
        }

        // [2.2] 无限循环让进程一直处于常驻状态。
        $EventModel = new Event();
        try {
            while(true) {
                $strEventVal = $redis->bRPopLPush($eventQueueKey, $eventQueueIngKey, 60);
                if ($strEventVal) {
                    $arrEventVal = json_decode($strEventVal, true);
                    // [2.3] 验证事件是否已存在。
                    $eventDetail = $EventModel->fetchOne([], ['id' => $arrEventVal['event_id']], '', '', true);
                    if (empty($eventDetail)) {
                        YLog::log(['data' => $arrEventVal, 'errMsg' => 'The database query does not exist!'], 'event', "{$code}-fail");
                        $redis->lRem($eventQueueIngKey, $strEventVal, 1);
                        continue;
                    }
                    // [2.4] 验证事件是否已经被消费。
                    if ($eventDetail['status'] != Event::STATUS_INIT) {
                        YLog::log(['data' => $arrEventVal, 'errMsg' => 'Message has been processed!'], 'event', "{$code}-fail");
                        $redis->lRem($eventQueueIngKey, $strEventVal, 1);
                        continue;
                    } else {
                        // [3] 调用具体的业务来处理这个消息。
                        try {
                            if ($arrEventVal['retry_count'] == 0 || (time() - $arrEventVal['last_time'] >= $interval)) {
                                static::runService($arrEventVal);
                            } else {
                                usleep(100000); // 0.1 秒。如果队列里面只剩一条错误的待处理的。如果不加以暂停处理会造成 CPU 负载 100。
                                continue;
                            }
                            $updata = [
                                'status' => Event::STATUS_SUCCESS,
                                'u_time' => date('Y-m-d H:i:s', time())
                            ];
                            $EventModel->update($updata, ['id' => $arrEventVal['event_id']]);
                            // [4] 处理成功将当前正在处理的删除。
                            $redis->lRem($eventQueueIngKey, $strEventVal, 1);
                            YLog::log($arrEventVal, 'event', "{$code}-success");
                        } catch (\Exception $e) {
                            // [4] 如果是业务错误。不进行第二次消费。如：卡券数量不足。
                            if ($e->getCode() == STATUS_SERVER_ERROR) {
                                $updata = [
                                    'status'     => Event::STATUS_FAIL,
                                    'u_time'     => date('Y-m-d H:i:s', time()),
                                    'error_code' => $e->getCode(),
                                    'error_msg'  => $e->getMessage()
                                ];
                                $EventModel->update($updata, ['id' => $arrEventVal['event_id']]);
                            } else if ($retryCount > 0 && $arrEventVal['retry_count'] <= $retryCount) {
                                $arrEventVal['retry_count'] += 1;
                                $arrEventVal['last_time']    = time();
                                $redis->lPush($eventQueueKey, json_encode($arrEventVal, JSON_UNESCAPED_UNICODE));
                            }
                            $redis->lRem($eventQueueIngKey, $strEventVal, 1);
                            $log = [
                                'value' => $strEventVal,
                                'code'  => $e->getCode(),
                                'msg'   => $e->getMessage()
                            ];
                            YLog::log($log, 'event', "{$code}-fail");
                        }
                    }
                } else {
                    $pong = $redis->ping();
                    if ($pong != '+PONG') {
                        YLog::log('Redis ping failure!', 'redis', 'ping');
                        YCore::exception(500, 'Redis ping failure!');
                    }
                    $EventModel->ping();
                    usleep(100000);
                }
            }
        } catch (\Exception $e) {
            $log = [
                'value' => $strEventVal,
                'code'  => $e->getCode(),
                'msg'   => $e->getMessage()
            ];
            YLog::log($log, 'event', "{$code}-error");
            $datetime = date('Y-m-d H:i:s', time());
            exit("Error Time:{$datetime}\nAn exception occurred in the event program!\n" . $e->getMessage());
        }
    }

    /**
     * 运行真实的业务。
     * 
     * -- 1) 当遇到严重错误，需要退出运行的时候直接抛出异常(如:自己可预知的异常/觉得必须退出的程序/程序代码错误)。
     * -- 2) 如果仅仅是遇到诸如条件不满足某某规则则只记录日志即可。保证进程继续处理下一个。
     * -- 注：
     * -- 1) 一定要防止事件被处理两次(已经在消费队列时做了数据库级别的检查)。
     * -- 2) 如果业务不满足则直接抛出 503 错误,则该事件消息不会再消费第二次。
     * -- 3）如果发生严重错误想重启进程并且需要给机会二次消费就给 500 错误。
     * 
     * @param array $event 事件数据。
     *
     * @return void
     */
    protected static function runService($event)
    {
        
    }
}