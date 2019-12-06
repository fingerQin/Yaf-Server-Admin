<?php
/**
 * 子事件基类。
 * -- 采用多进程支持。实现数据消费的高吞吐。
 * @author fingerQin
 * @date 2018-09-13
 */

namespace Services\Event\Sub;

use finger\App;
use finger\Cache;
use finger\Database\Db;
use Models\Event;

abstract class AbstractBase extends \finger\Thread\Thread
{
    /**
     * 事件 Code。
     * 
     * @var string
     */
    protected $code = '';

    /**
     * 事件消费失败重试次数。
     *
     * @var int
     */
    protected $retryCount = 0;

    /**
     * 事件消费失败
     *
     * @var int
     */
    protected $interval = 0;

    /**
     * 设置事件 code。
     * 
     * @param  string  $code  事件 code。
     * 
     * @return void
     */
    public function setEventCode($code)
    {
        $this->code = $code;
    }

    /**
     * 设置事件消费失败重试的次数。
     *
     * @param  int  $retryCount  重试次数。
     *
     * @return void
     */
    public function setRetryCount($retryCount)
    {
        $this->retryCount = $retryCount;
    }

    /**
     * 设置事件消费失败重试的时间间隔。
     *
     * @param  int  $interval  时间间隔。
     *
     * @return void
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
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
    protected function runService($event) {}

    /**
     * 抽象的业务方法。
     * 
     * -- 注意
     * -- 1) 该方法只能由 PHP CLI 模式调用执行。
     * -- 2) 执行该方法之前不要有任何的 Redis 连接操作。
     * -- 3) 如果是单进程运行该方法可以忽略第 2 点。如果是多进程运行，必须保证第二点。因为，Redis 阻塞只能由多个 Redis 连接操作。
     * 
     * @param  int  $threadNum     进程数量。
     * @param  int  $num           子进程编号。
     * @param  int  $startTimeTsp  子进程启动时间戳。
     * 
     * @return void
     */
    public function run($threadNum, $num, $startTimeTsp)
    {
        // [1]
        $redis            = Cache::getRedisClient();
        $eventQueueKey    = \Services\Event\AbstractBase::EVENT_PREFIX . '_' . $this->code;
        $eventQueueIngKey = "{$eventQueueKey}-{$num}-ing";

        // [2] 无限循环处理消息队列的数据。
        // [2.1] 将当前正在处理的事件归恢复到事件池中。主要是预防进程重启导致正在处理的事件未正确处理。
        while ($redis->rPopLPush($eventQueueIngKey, $eventQueueKey)) {
            // 因为仅仅是利用 while 语句的循环特性,所以这里不需要实际业务代码。
        }

        // [2.2] 无限循环让进程一直处于常驻状态。
        $EventModel = new Event();
        try {
            while(true) {
                $strEventVal = $redis->bRPopLPush($eventQueueKey, $eventQueueIngKey, 1);
                if ($strEventVal) {
                    $arrEventVal = json_decode($strEventVal, true);
                    // [2.3] 验证事件是否已存在。
                    $eventDetail = $EventModel->fetchOne([], ['id' => $arrEventVal['event_id']], '', '', true);
                    if (empty($eventDetail)) {
                        App::log(['data' => $arrEventVal, 'errMsg' => 'The database query does not exist!'], 'event', "{$this->code}-fail");
                        $redis->lRem($eventQueueIngKey, $strEventVal, 1);
                        continue;
                    }
                    // [2.4] 验证事件是否已经被消费。
                    if ($eventDetail['status'] != Event::STATUS_INIT) {
                        App::log(['data' => $arrEventVal, 'errMsg' => 'Message has been processed!'], 'event', "{$this->code}-fail");
                        $redis->lRem($eventQueueIngKey, $strEventVal, 1);
                        continue;
                    } else {
                        // [3] 调用具体的业务来处理这个消息。
                        try {
                            $time     = time();
                            $lastTime = $arrEventVal['last_time'];
                            $diffTime = $time - $lastTime;
                            if ($arrEventVal['retry_count'] == 0 || ($diffTime >= $this->interval)) {
                                $this->runService($arrEventVal);
                            } else {
                                $redis->lPush($eventQueueKey, json_encode($arrEventVal, JSON_UNESCAPED_UNICODE));
                                $redis->lRem($eventQueueIngKey, $strEventVal, 1);
                                usleep(200000); // 0.2 秒。如果队列里面只剩一条错误的待处理的。如果不加以暂停处理会造成 CPU 负载 100。
                                continue;
                            }
                            $updata = ['status' => Event::STATUS_SUCCESS];
                            $EventModel->update($updata, ['id' => $arrEventVal['event_id']]);
                            // [4] 处理成功将当前正在处理的删除。
                            $redis->lRem($eventQueueIngKey, $strEventVal, 1);
                            App::log($arrEventVal, 'event', "{$this->code}-success");
                        } catch (\Exception $e) {
                            // [4] 如果是业务错误。不进行第二次消费。如：卡券数量不足。
                            if ($e->getCode() == STATUS_SERVER_ERROR) {
                                $this->eventToFail($this->code, $strEventVal, $arrEventVal['event_id'], $e->getCode(), $e->getMessage());
                            } else if ($this->retryCount > 0 && $arrEventVal['retry_count'] <= $this->retryCount) {
                                $arrEventVal['retry_count'] += 1;
                                $arrEventVal['last_time']    = time();
                                $redis->lPush($eventQueueKey, json_encode($arrEventVal, JSON_UNESCAPED_UNICODE));
                            } else { // 重试结束且依然失败。
                                $this->eventToFail($this->code, $strEventVal, $arrEventVal['event_id'], $e->getCode(), $e->getMessage());
                            }
                            $redis->lRem($eventQueueIngKey, $strEventVal, 1);
                        }
                    }
                } else {
                    Db::ping();
                    Cache::ping();
                }
                $this->isExit($startTimeTsp);
            }
        } catch (\Exception $e) {
            $this->exceptionExit($this->code, $strEventVal, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 事件消费失败。
     * 
     * @param  string  $code     事件编码。
     * @param  string  $event    队列取出来的数据。
     * @param  int     $eventId  事件 ID。
     * @param  int     $errCode  错误码。
     * @param  string  $errMsg   错误消息。
     * 
     * @return void
     */
    protected function eventToFail($code, $event, $eventId, $errCode, $errMsg)
    {
        // [1] 更新数据库记录。
        $updata = [
            'status'     => Event::STATUS_FAIL,
            'error_code' => $errCode,
            'error_msg'  => $errMsg
        ];
        $EventModel = new Event();
        $EventModel->update($updata, ['id' => $eventId]);
        // [2] 记录文件日志。
        $log = [
            'value' => $event,
            'code'  => $errCode,
            'msg'   => $errMsg
        ];
        App::log($log, 'event', "{$code}-fail");
        unset($EventModel, $updata, $log);
    }

    /**
     * 异常退出。
     *
     * @param  string  $code     事件 code。
     * @param  string  $event    队列取出来的数据。
     * @param  int     $errCode  错误码。
     * @param  string  $errMsg   错误信息。
     *
     * @return void
     */
    protected function exceptionExit($code, $event, $errCode, $errMsg)
    {
        $log = [
            'value' => $event,
            'code'  => $errCode,
            'msg'   => $errMsg
        ];
        App::log($log, 'event', "{$code}-error");
        $datetime = date('Y-m-d H:i:s', time());
        exit("ErrorTime:{$datetime}\nErrorMsg:{$errMsg}\n");
    }
}