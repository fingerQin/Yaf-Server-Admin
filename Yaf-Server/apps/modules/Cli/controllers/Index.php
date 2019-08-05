<?php
/**
 * 默认 CLI 控制器。
 * @author fingerQin
 * @date 2018-08-16
 */

use Utils\YExcel;
use Models\Event;
use Services\Event\Producer;

class IndexController extends \Common\controllers\Cli
{
    public function indexAction()
    {
        $headerTitle = ['姓名', '性别', '年龄'];
        $data = [
            ['张三', '男', '28'],
            ['张三', '男', '28'],
            ['张三', '男', '28'],
            ['张三', '男', '28']
        ];
        YExcel::createExcel($headerTitle, $data, __DIR__, 'tests');
    }

    /**
     * 登录事件数据 Push。
     */
    public function threadPushAction()
    {
        $datetime  = date('Y-m-d H:i:s', time());
        for ($i = 0; $i < 1000; $i++) {
            Producer::push([
                'code'        => Event::CODE_LOGIN,
                'userid'      => 1,
                'mobile'      => '18575202691',
                'platform'    => 1,
                'app_v'       => '0.0.1',
                'v'           => '1.0.0',
                'login_time'  => $datetime
            ]);
        }
        echo "ok:{$datetime}\n";
    }

    public function testAction()
    {
        $datetime = date('Y-m-d H:i:s');
        $item = [
            'code'       => 'register',
            'userid'     => '0',
            'error_code' => '0',
            'error_msg'  => '',
            'status'     => '1',
            'data'       => 'sssss',
            'u_time'     => $datetime,
            'c_time'     => $datetime
        ];
        $data = [];
        for ($i = 0; $i < 1000; $i++) {
            $data[] = $item;
        }
        $EventModel = new Event();
        $EventModel->insertAll($data);
    }
}