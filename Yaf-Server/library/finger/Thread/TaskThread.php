<?php
/**
 * 业务多线程处理。
 * 
 * -- 该文件只是一个测试示例。请把你的业务不要定义在这里。你可以在任何地方继承 Thread 类。然后实现其 run() 方法。
 * 
 * @author fingerQin
 * @date 2017-09-15
 */

namespace finger\Thread;

class TaskThread extends Thread
{
    /**
     * 业务运行方法。
     * 
     * -- 在 run 中编写的方法请一定要确定是事务型的。要么成功要么失败。要处于好失败情况下的数据处理。
     * 
     * @param  int  $threadNum     进程数量。
     * @param  int  $num           当前子进程编号。此编号与当前进程数量对应。比如，你有一个业务需要10个进程处理，每个进行处理其中的10分之一的数量。此时可以根据此值取模。
     * @param  int  $startTimeTsp  子进程启动时间戳。
     * 
     * @return void
     */
    public function run($threadNum, $num, $startTimeTsp)
    {
        while (true) {
            sleep(1);
            $this->isExit($startTimeTsp);
        }
    }
}
