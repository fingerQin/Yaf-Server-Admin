<?php
/**
 * 事件控制器。
 * @author fingerQin
 * @date 2018-09-13
 */

use finger\RedisMutexLock;
use finger\Database\Db;
use Utils\YCache;
use Models\Event;
use Services\Event\Consumer;
use Services\Event\Sub\Login;
use Services\Event\Sub\Register;

class EventController extends \Common\controllers\Cli
{
    /**
     * 事件分发常驻进程。
     * 
     * -- 启动方式：php cli.php event/dispatcher
     *
     */
    public function dispatcherAction()
    {
        Consumer::dispatcher();
    }

    /**
     * 注册事件消费常驻进程。
     * 
     * -- 启动方式：php cli.php event/register
     *
     */
    public function registerAction()
    {
        Register::launch(Event::CODE_REGISTER);
    }

    /**
     * 登录事件消费常驻进程。
     * 
     * -- 启动方式：php cli.php event/login
     *
     */
    public function loginAction()
    {
        Login::launch(Event::CODE_LOGIN);
    }
}