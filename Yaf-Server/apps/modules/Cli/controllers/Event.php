<?php
/**
 * 事件控制器。
 * @author fingerQin
 * @date 2018-09-13
 */

use Models\Event;
use Services\Event\Consumer;
use Services\Event\Sub\Login;
use Services\Event\Sub\Register;

class EventController extends \Common\controllers\Cli
{
    /**
     * 事件分发常驻进程。
     * 
     * -- 进入项目根目录下的 public 文件夹，然后如下方式启动：
     * -- $ php cli.php Event/dispatcher
     *
     */
    public function dispatcherAction()
    {
        Consumer::dispatcher();
    }

    /**
     * 注册事件消费常驻进程。
     * 
     * -- 进入项目根目录下的 public 文件夹，然后如下方式启动：
     * -- $ php cli.php Event/register
     *
     */
    public function registerAction()
    {
        $objThread = Register::getInstance(5);
        $objThread->setChildOverNewCreate(true);
        $objThread->setRunDurationExit(30);
        $objThread->setEventCode(Event::CODE_REGISTER);
        $objThread->setRetryCount(0);
        $objThread->setInterval(0);
        $objThread->start();
    }

    /**
     * 登录事件消费常驻进程。
     * 
     * -- 进入项目根目录下的 public 文件夹，然后如下方式启动：
     * -- $ php cli.php Event/login
     *
     */
    public function loginAction()
    {
        $objThread = Login::getInstance(5);
        $objThread->setChildOverNewCreate(true);
        $objThread->setRunDurationExit(30);
        $objThread->setEventCode(Event::CODE_LOGIN);
        $objThread->setRetryCount(0);
        $objThread->setInterval(0);
        $objThread->start();
    }
}