<?php
/**
 * 统计定时器。
 * @author fingerQin
 * @date 2019-05-28
 */

use Services\Stats\Sms\Stats;

class StatsController extends \Common\controllers\Cli
{
    /**
     * 短信发送统计定时器。
     * 
     * -- 每隔一小时统计一次。时间最好在每小时的第 5 分钟。
     */
    public function sendAction()
    {
        $datetime = $this->getString('datetime', '');
        Stats::send($datetime);
    }
}