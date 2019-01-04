<?php
/**
 * 默认 CLI 控制器。
 * @author fingerQin
 * @date 2018-08-16
 */

use Threads\DemoThread;

class ThreadController extends \Common\controllers\Cli
{
    /**
     * 启动 demo 多进程(常驻进程)。
     */
    public function demoAction()
    {
        $objThread = DemoThread::getInstance(5);
        $objThread->start();
    }
}