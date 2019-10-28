<?php
/**
 * 默认 CLI 控制器。
 * @author fingerQin
 * @date 2018-08-16
 */

use finger\Utils\YExcel;
use Models\Event;
use Threads\DemoThread;

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
        $objThread = DemoThread::getInstance(5);
        $objThread->setChildOverNewCreate(false);
        $objThread->setRunDurationExit(30);
        $objThread->start();
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