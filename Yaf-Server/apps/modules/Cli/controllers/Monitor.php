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
     * -- 进入项目根目录下的 public 文件夹，然后如下方式启动：
     * -- $ php cli.php Monitor/consumer
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