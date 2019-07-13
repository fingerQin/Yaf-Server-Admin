<?php
/**
 * 监控数据消费。
 * -- 采用多进程支持。实现数据消费的高吞吐。
 * @author fingerQin
 * @date 2019-06-13
 */

namespace Services\Monitor;

use Utils\YLog;
use Utils\YCache;
use Utils\YCore;
use Models\Monitor;
use finger\Database\Db;

class Consumer extends \finger\Thread\Thread
{
    /**
     * 运行真实的业务。
     * 
     * @param array $data 队列数据。
     *
     * @return bool true 消费成功、false - 消费失败。
     */
    protected function runService($data)
    {
        $className = ucfirst($data['code']);
        $className = "\\Services\\Monitor\\Sub\\{$className}";
        return $className::runService($data);
    }

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
        $redis              = YCache::getRedisClient();
        $monitorQueueKey    = \Services\Monitor\AbstractBase::MONITOR_QUEUE_KEY;
        $monitorQueueIngKey = "{$monitorQueueKey}-{$num}-ing"; // 如果不加子进程编号，则多个子进程同时处理时会出现多进程同时消费情况。

        // [2] 无限循环处理消息队列的数据。
        // [2.1] 将当前正在处理的事件归恢复到事件池中。主要是预防进程重启导致正在处理的事件未正确处理。
        while ($redis->rPopLPush($monitorQueueIngKey, $monitorQueueKey)) {
            // 因为仅仅是利用 while 语句的循环特性,所以这里不需要实际业务代码。
        }

        // [2.2] 无限循环让进程一直处于常驻状态。
        $MonitorModel = new Monitor();
        try {
            $batResult = []; // 保存每批次入库的数据。
            while(true) {
                $strQueueVal = $redis->rPopLPush($monitorQueueKey, $monitorQueueIngKey);
                if ($strQueueVal) {
                    $arrQueueVal = json_decode($strQueueVal, true);
                    // [3] 调用具体的业务来处理这个消息。
                    try {
                        if (count($batResult) >= 100) {
                            $this->monitorToSuccess($batResult);
                        }
                        $this->runService($arrQueueVal);
                        $redis->lRem($monitorQueueIngKey, $strQueueVal, 1);
                        YLog::log($arrQueueVal, 'monitor', "{$arrQueueVal['code']}-success");
                    } catch (\Exception $e) {
                        $this->monitorToFail($arrQueueVal['code'], $strQueueVal, $e->getCode(), $e->getMessage());
                        $redis->lRem($monitorQueueIngKey, $strQueueVal, 1);
                    }
                } else {
                    $pong = $redis->ping();
                    if ($pong != '+PONG') {
                        YLog::log('Redis ping failure!', 'redis', 'monitor-ping');
                        YCore::exception(STATUS_ERROR, 'Redis ping failure!');
                    }
                    $MonitorModel->ping();
                    $this->monitorToSuccess($redis, $num);
                    usleep(100000);
                }
                $this->isExit($startTimeTsp);
            }
        } catch (\Exception $e) {
            $code = $arrQueueVal['code'] ?? '';
            $this->exceptionExit($code, $strQueueVal, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 监控上报数据入库。
     * 
     * @param  array  $batResult  一组待入库数据。
     * 
     * @return void
     */
    protected function monitorToSuccess(&$batResult)
    {
        if (!empty($data)) {
            $params = [];
            $sql = 'REPLACE INTO finger_monitor(serial_no,code,userid,`status`,`data`,c_time) VALUES ';
            foreach ($batResult as $k => $item) {
                $monitor  = json_decode($item, true);
                $userid   = $monitor['userid'] ?? 0;
                $status   = 0;
                $datetime = $monitor['datetime'];
                $sql .= "(:serial_no_{$k},:code_{$k},:userid_{$k},:status_{$k},:data_{$k},:c_time_{$k}),";
                $params[":serial_no_{$k}"] = $monitor['serial_no'];
                $params[":code_{$k}"]      = $monitor['code'];
                $params[":data_{$k}"]      = $item;
                $params[":userid_{$k}"]    = $userid;
                $params[":status_{$k}"]    = $status;
                $params[":c_time_{$k}"]    = $datetime;
            }
            $sql = rtrim($sql, ',');
            Db::execute($sql, $params);
        }
    }

    /**
     * 队列消费失败。
     * 
     * @param  string  $code      监控编码。
     * @param  string  $data      队列取出来的数据。
     * @param  int     $errCode   错误码。
     * @param  string  $errMsg    错误消息。
     * 
     * @return void
     */
    protected function monitorToFail($code, $data, $errCode, $errMsg)
    {
        $log = [
            'value' => $data,
            'code'  => $errCode,
            'msg'   => $errMsg
        ];
        YLog::log($log, 'monitor', "{$code}-fail");
        unset($MonitorModel, $updata, $log);
    }

    /**
     * 异常退出。
     *
     * @param  string  $code     事件 code。
     * @param  string  $data     队列取出来的数据。
     * @param  int     $errCode  错误码。
     * @param  string  $errMsg   错误信息。
     *
     * @return void
     */
    protected function exceptionExit($code, $data, $errCode, $errMsg)
    {
        $log = [
            'value' => $data,
            'code'  => $errCode,
            'msg'   => $errMsg
        ];
        YLog::log($log, 'monitor', "{$code}-error");
        $datetime = date('Y-m-d H:i:s', time());
        exit("ErrorTime:{$datetime}\nErrorMsg:{$errMsg}\n");
    }
}