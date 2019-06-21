<?php
/**
 * 默认 CLI 控制器。
 * @author fingerQin
 * @date 2018-08-16
 */

use Utils\YExcel;

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
}