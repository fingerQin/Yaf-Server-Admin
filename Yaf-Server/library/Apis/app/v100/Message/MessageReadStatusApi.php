<?php
/**
 * 系统消息已读设置接口。
 * 
 * @author fingerQin
 * @date 2019-04-22
 * @version 1.0.0
 */

namespace Apis\app\v100\Message;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\System\Message;

class MessageReadStatusApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token    = $this->getString('token', '');
        $userinfo = Auth::checkAuth($token);
        $msgid    = $this->getInt('msgid');
        Message::read($userinfo['userid'], $msgid);
        $this->render(STATUS_SUCCESS, '设置成功');
    }
}