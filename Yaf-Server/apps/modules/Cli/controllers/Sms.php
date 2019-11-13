<?php
/**
 * 短信定时器和常驻进程。
 * @author fingerQin
 * @date 2018-09-14
 */

use Services\Sms\Consume;

class SmsController extends \Common\controllers\Cli
{
    /**
     * 短信发送消费常驻进程。
     *
     * -- 进入项目根目录下的 public 文件夹，然后如下方式启动：
     * -- $ php cli.php Sms/send
     * 
     */
    public function sendAction()
    {
        Consume::sendSms();
    }
}