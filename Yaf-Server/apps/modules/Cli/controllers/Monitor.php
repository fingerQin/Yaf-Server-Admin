<?php
/**
 * 监控上报控制器。
 * @author fingerQin
 * @date 2019-06-17
 */

use Services\Monitor\Consumer;

class MonitorController extends \Common\controllers\Cli
{
    /**
     * 注册事件消费常驻进程。
     * 
     * -- 启动方式：php cli.php Monitor/consumer
     *
     */
    public function consumerAction()
    {
        $objThread = Consumer::getInstance(5);
        $objThread->setChildOverNewCreate(true);
        $objThread->setRunDurationExit(3600);
        $objThread->start();
    }
}