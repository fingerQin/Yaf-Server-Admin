<?php
/**
 * 用户详情接口。
 * 
 * @author fingerQin
 * @date 2018-08-15
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\User\User;

class UserDetailApi extends AbstractApi
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
        $detail   = User::detail($userinfo['userid']);
        $this->render(STATUS_SUCCESS, 'success', $detail);
    }
}