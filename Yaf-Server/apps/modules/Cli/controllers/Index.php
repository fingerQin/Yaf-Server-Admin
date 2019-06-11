<?php
use finger\Ip;
use Utils\YCache;
use Models\Event;
use Services\Event\Producer;

/**
 * 默认 CLI 控制器。
 * @author fingerQin
 * @date 2018-08-16
 */

class IndexController extends \Common\controllers\Cli
{
    public function indexAction()
    {
        $ip = '192.168.56.11';
        $result = Ip::isRange('192.168.56.10', '192.168.56.255', $ip);
        var_dump($result);
        $int = ip2long($ip);
        echo $int;
        echo "\n";
        echo long2ip($int);
        echo "\n";
        exit;
    }

    public function redisAction()
    {
        $redis = YCache::getRedisClient('second');
        $redis->set('xxx', '123');
    }

    /**
     * 多进程测试。
     */
    public function processAction()
    {
        cli_set_process_title('YafServerProcess');
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