<?php
/**
 * 手机号更改接口。
 * 
 * @author fingerQin
 * @date 2019-04-26
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\User\User;

class UserMobileChangeApi extends AbstractApi
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
        $password = $this->getString('password', '');
        $mobile   = $this->getString('mobile', '');
        $code     = $this->getString('code', '');
        User::changeMobile($userinfo['userid'], $password, $mobile, $code);
        $this->render(STATUS_SUCCESS, '手机号更改成功');
    }
}