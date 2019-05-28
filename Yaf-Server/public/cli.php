<?php
/**
 * 命令行运行入口文件。
 * 
 * @author fingerQin
 * @date 2018-06-27
 */

error_reporting(0);
define('TIMESTAMP', time());
ini_set('default_socket_timeout', -1);
define('APP_PATH', dirname(dirname(__FILE__)));

require(APP_PATH . '/vendor/autoload.php');
require(APP_PATH . '/config/constants.php');

(new \Yaf_Application(APP_PATH . "/config/config.ini", 'conf'))->bootstrap();

if (!isset($argv[1])) {
    exit("Please enter the route to execute. Example: the php cli.php Index/Index!\n");
}

$routeArr = explode('/', $argv[1]);
if (count($routeArr) != 2) {
    exit("Please enter the route to execute. Example: the php cli.php Index/Index!\n");
}

$controllerName = $routeArr[0];
$actionName     = $routeArr[1];

// 删除路由参数。
unset($argv[0], $argv[1]);
$params = [];
parse_str($argv[2], $params);

$request = new \Yaf_Request_Simple('CLI', 'Cli', $controllerName, $actionName, $params);
\Yaf_Application::app()->getDispatcher()->returnResponse(true)->dispatch($request);