<?php
/**
 * 公共的引导程序。
 * -- 1、以_init开头的方法, 都会被Yaf调用。非_init方法不会被调用。
 * -- 2、所有方法都接受一个参数:\\Yaf\Dispatcher $dispatcher调用的次序, 和申明的次序相同。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common;

use Utils\YUrl;
use Utils\YCore;
use Utils\YLog;
use Utils\YCache;

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
     * -- 1、开/关 PHP 错误。
     * -- 2、接管 PHP 错误。
     */
    public function _initError()
    {
        $config = \Yaf_Registry::get('config');
        ini_set('display_errors', 0);
        set_error_handler(['\Utils\YCore', 'errorHandler']);
        register_shutdown_function(['\Utils\YCore', 'registerShutdownFunction']);
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
        if (YCore::appconfig('session.status')) {
            $redis   = YCache::getRedisClient();
            $sess    = new \finger\session\redis\SessionHandler($redis, null, 'sess_');
            session_set_save_handler($sess);
            $session = \Yaf_Session::getInstance();
            \Yaf_Registry::set('session', $session);
        }
    }

    /**
     * 记录访问日志。
     *
     * @param \Yaf_Dispatcher $dispatcher
     */
    public function _initAccessLog(\Yaf_Dispatcher $dispatcher)
    {
        $request    = $dispatcher->getRequest();
        $ip         = YCore::ip();
        $url        = YUrl::getUrl();
        $postParams = $request->getPost();
        // 因为 API 已经记录了访问日志。这里不再记录。
        // if (PHP_SAPI != 'cli') { // CLI 模式不记录访问日志。
        //     YLog::log(['ip' => $ip, 'url' => $url, 'params' => $postParams], 'accessLog', 'log');
        // }
    }
}
