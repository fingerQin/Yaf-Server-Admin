<?php
use finger\Ip;

/**
 * 默认 CLI 控制器。
 * @author fingerQin
 * @date 2018-08-16
 */

class IndexController extends \Common\controllers\Cli
{
    public function indexAction()
    {
        $ip = '192.168.56.11';
        $result = Ip::isRange('192.168.56.10', '192.168.56.255', $ip);
        var_dump($result);
        $int = ip2long($ip);
        echo $int;
        echo "\n";
        echo long2ip($int);
        echo "\n";
        exit;
    }
}