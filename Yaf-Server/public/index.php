<?php
/**
 * 前台入口文件。
 * @author fingerQin
 * @date 2018-06-27
 */

define('MICROTIME', microtime());
define('TIMESTAMP', time());
define('APP_PATH', dirname(dirname(__FILE__)));
require(APP_PATH . '/vendor/autoload.php');
require(APP_PATH . '/config/constants.php');
$app = new \Yaf_Application(APP_PATH . '/config/config.ini', 'conf');
$app->bootstrap()->run();