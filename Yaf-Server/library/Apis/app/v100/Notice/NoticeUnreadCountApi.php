<?php
/**
 * 用户公告未读数量接口。
 * 
 * @author fingerQin
 * @date 2019-04-22
 * @version 1.0.0
 */

namespace Apis\app\v100\Notice;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\System\Notice;

class NoticeUnreadCountApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token  = $this->getString('token', '');
        $userid = Auth::getTokenUserId($token);
        $count  = Notice::unreadCount($userid);
        $this->render(STATUS_SUCCESS, 'success', ['count' => $count]);
    }
}