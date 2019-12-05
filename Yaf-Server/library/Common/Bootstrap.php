<?php
/**
 * 公共的引导程序。
 * -- 1、以_init开头的方法, 都会被Yaf调用。非_init方法不会被调用。
 * -- 2、所有方法都接受一个参数:\\Yaf\Dispatcher $dispatcher调用的次序, 和申明的次序相同。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common;

use finger\App;
use finger\Utils\YCache;

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,这些方法,
 * 都接受一个参数:\\Yaf\Dispatcher $dispatcher调用的次序, 和申明的次序相同。
 */
class Bootstrap extends \Yaf_Bootstrap_Abstract
{
    /**
     * 注册配置到全局环境。
     * -- 1、率先执行，以便后续的程序都能读取到配置文件。
     */
    public function _initConfig()
    {
        $config = \Yaf_Application::app()->getConfig();
        \Yaf_Registry::set('config', $config);
        date_default_timezone_set($config->get('app.timezone'));
    }

    /**
     * 错误相关操作初始化。
     */
    public function _initError()
    {
        ini_set('display_errors', 0);
        set_error_handler(['\finger\Utils\YCore', 'errorHandler']);
        register_shutdown_function(['\finger\Utils\YCore', 'registerShutdownFunction']);
    }

    /**
     * 初始化 session 到 reids 中。
     * --------------------------------------
     * 1、实现 SessionHandlerInterface 接口,将 session 保存到 reids 中。
     * 2、重新开启 session,让默认的 session 切换到自已的 session 接口。
     * 3、第二步中直接影响 \Yaf_Session 的工作方式。
     * 4、或者直接关闭 SESSION 的使用。
     * --------------------------------------
     */
    public function _initSession(\Yaf_Dispatcher $dispatcher)
    {
        if (App::getConfig('session.status')) {
            $redis   = YCache::getRedisClient();
            $sess    = new \finger\session\redis\SessionHandler($redis, null, 'sess_');
            session_set_save_handler($sess);
            $session = \Yaf_Session::getInstance();
            \Yaf_Registry::set('session', $session);
        }
    }

    /**
     * 注册插件。
     * --1、Yaf框架会根据特有的类名后缀(Model、Controller、Plugin)进行自动加载。为避免这种情况请不要以这样的名称结尾。
     * --2、 插件可能会用到缓存、数据库、配置等。所以，放到最后执行。
     *
     * @param \Yaf\Dispatcher $dispatcher
     */
    public function _initPlugin(\Yaf_Dispatcher $dispatcher)
    {
        $plugin = new \Common\plugins\Router();
        $dispatcher->registerPlugin($plugin);
    }
}
