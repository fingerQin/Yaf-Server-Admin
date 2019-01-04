<?php
/**
 * 短信定时器和常驻进程。
 * @author fingerQin
 * @date 2018-09-14
 */

use Utils\YLog;
use Services\Sms\Consume;

class SmsController extends \Common\controllers\Cli
{
    public function sendAction()
    {
        Consume::sendSms();
    }
}