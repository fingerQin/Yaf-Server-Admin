<?php
/**
 * 多进程 Demo 示例。
 * @author fingerQin
 * @date 2018-09-18
 */

namespace Threads;

use finger\Thread\Thread;
use Services\Event\Sub\Login;
use Models\Event;

class DemoThread extends Thread
{
    /**
     * 业务运行方法。
     * 
     * -- 在 run 中编写的方法请一定要确定是事务型的。要么成功要么失败。要处于好失败情况下的数据处理。
     * 
     * @param int $num 当前子进程编号。此编号与当前进程数量对应。比如，你有一个业务需要10个进程处理，每个进行处理其中的10分之一的数量。此时可以根据此值取模。
     * 
     * @return void
     */
    public function run($threadNum, $num)
    {
        Login::launch(Event::CODE_LOGIN);
    }
}