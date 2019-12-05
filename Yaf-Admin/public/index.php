<?php
/**
 * 前台入口文件。
 * -- 1、入口文件会判断当前域名去加载配置文件。
 * @author fingerQin
 * @date 2018-06-27
 */

use finger\App;

define('MICROTIME', microtime());
define('TIMESTAMP', time());
define('APP_PATH', dirname(dirname(__FILE__)));
require(APP_PATH . '/config/constants.php');
require(APP_PATH . '/vendor/autoload.php');
$app = new \Yaf_Application(APP_PATH . '/config/config.ini', 'conf');
// 根据是否存在 .env 进行配置文件的加载。
if (file_exists('../.env')) {
    $cfgObj = new \Yaf_Config_Ini('../.env', '');
    $config = $cfgObj->toArray();
} else {
    $config = $app->getConfig()->toArray();
}
(new App($config));
$app->bootstrap()->run();