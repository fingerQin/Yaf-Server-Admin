<?php
/**
 * 多进程 Demo 示例。
 * @author fingerQin
 * @date 2018-09-18
 */

namespace Threads;

use Models\Event;
use finger\Thread\Thread;
use Services\Event\Producer;

class DemoThread extends Thread
{
    /**
     * 业务运行方法。
     * 
     * -- 在 run 中编写的方法请一定要确定是事务型的。要么成功要么失败。要处于好失败情况下的数据处理。
     * 
     * @param int $threadNum    进程总数量。
     * @param int $num          当前子进程编号。此编号与当前进程数量对应。比如，你有一个业务需要10个进程处理，每个进行处理其中的10分之一的数量。此时可以根据此值取模。
     * @param int $startTimeTsp 启动时间戳。
     * 
     * @return void
     */
    public function run($threadNum, $num, $startTimeTsp)
    {
        // sleep(3);
        $datetime = date('Y-m-d H:i:s', time());
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
        echo "ok:{$datetime}:{$num}\n";
    }
}