<?php
/**
 * 短信管理。
 * @author fingerQin
 * @date 2018-07-07
 */

use finger\Paginator;
use Services\Sms\Log;
use Services\Sms\Tpl;
use Services\Sms\Channel;

class SmsController extends \Common\controllers\Admin
{
    /**
     * 短信发送日志列表。
     */
    public function listsAction()
    {
        $mobile    = $this->getString('mobile', '');
        $status    = $this->getInt('status', -1);
        $tplId     = $this->getInt('tpl_id', -1);
        $channelId = $this->getInt('channel_id', -1);
        $startTime = $this->getString('start_time', '');
        $endTime   = $this->getString('end_time', '');
        $page      = $this->getInt('page', 1);
        $result    = Log::lists($mobile, $status, $tplId, $channelId, $startTime, $endTime, $page, 20);
        $paginator = new Paginator($result['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('result', $result);
        $this->assign('mobile', $mobile);
        $this->assign('status', $status);
        $this->assign('tpl_id', $tplId);
        $this->assign('channel_id', $channelId);
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('list', $result['list']);
        $this->assign('pageHtml', $pageHtml);
        $this->assign('tpls', Tpl::all());
        $this->assign('channels', Channel::all());
    }
}