<?php
/**
 * APP 接口默认 controller。
 * @author fingerQin
 * @date 2018-06-27
 */

use Apis\Factory;
use Utils\YCore;

class IndexController extends \Common\controllers\Api
{
    /**
     * API入口。
     * 普通 POST 方式提交。
     */
    public function indexAction()
    {
        // [1]
        define('IS_API', true);
        header("Access-Control-Allow-Origin: *");
        header('Content-type: application/json');
        // [2]
        $params = [
            'post'  => $this->_request->getPost(),
            'input' => file_get_contents('php://input')
        ];
        // [3]
        $apiObj = Factory::factory($params);
        $result = $apiObj->renderJson();
        // [4]
        \Utils\YLog::writeApiResponseLog($result);
        echo $result;
        // [5]
        $this->end();
    }
}