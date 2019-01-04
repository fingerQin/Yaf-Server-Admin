<?php
/**
 * 用户登录接口。
 * @author fingerQin
 * @date 2018-07-17
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;

class UserLogoutApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @see Api::runService()
     * @return void
     */
    protected function runService()
    {
        $token    = $this->getString('token', '');
        $platform = $this->getString('platform');
        $userid   = Auth::getTokenUserId($token);
        $result   = Auth::logout($userid, $platform);
        $this->render(STATUS_SUCCESS, '退出成功', $result);
    }
}