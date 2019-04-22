<?php
/**
 * 系统消息列表接口。
 * 
 * @author fingerQin
 * @date 2019-04-22
 * @version 1.0.0
 */

namespace Apis\app\v100\Message;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\System\Message;

class MessageListApi extends AbstractApi
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
        $page     = $this->getInt('page', 1);
        $result   = Message::lists($userinfo['userid'], $page, 20);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}