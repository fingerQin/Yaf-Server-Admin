<?php
/**
 * 默认 CLI 控制器。
 * @author fingerQin
 * @date 2018-08-16
 */

use Utils\YCache;
use Models\Event;
use Services\Event\Producer;

class IndexController extends \Common\controllers\Cli
{
    public function indexAction()
    {
        echo "finished!\n";
    }

    /**
     * 监控数据上报 Push。
     */
    public function monitorPushAction()
    {
        for ($i = 0; $i < 10000; $i++) {
            $data = [
                'code'     => 'unauthorized',
                'ip'       => '192.168.56.1',
                'datetime' => date('Y-m-d H:i:s', TIMESTAMP)
            ];
            \Services\Monitor\Producer::push($data);
        }
        echo "finished!\n";
    }

    /**
     * 监控数据消费。
     */
    public function monitorConsumerAction()
    {
        $objThread = \Services\Monitor\Consumer::getInstance(5);
        $objThread->setChildOverNewCreate(true);
        $objThread->setRunDurationExit(60);
        $objThread->start();
    }

    /**
     * 多进程测试。
     */
    public function processAction()
    {
        $objThread = \finger\Thread\TaskThread::getInstance(5);
        $objThread->setChildOverNewCreate(true);
        $objThread->setRunDurationExit(60);
        $objThread->start();
    }

    /**
     * 给多进程(线程)持续放入数据。
     * 
     * -- 定时启动。
     */
    public function threadPushAction()
    {
        $datetime  = date('Y-m-d H:i:s', time());
        for ($i = 0; $i < 100; $i++) {
            Producer::push([
                'code'        => Event::CODE_LOGIN,
                'userid'      => 1,
                'mobile'      => '18575202691',
                'platform'    => 1,
                'app_v'       => '0.0.1',
                'v'           => '1.0.0',
                'login_time'  => $datetime
            ]);
        }
        echo "ok:{$datetime}\n";
    }
}