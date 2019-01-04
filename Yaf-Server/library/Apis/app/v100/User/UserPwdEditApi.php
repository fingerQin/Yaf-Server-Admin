<?php
/**
 * 用户密码修改接口。
 * 
 * @author fingerQin
 * @date 2018-08-15
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\User\Password;

class UserPwdEditApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token    = $this->getString('token', '');
        $oldPwd   = $this->getString('old_pwd', '');
        $newPwd   = $this->getstring('new_pwd', '');
        $userinfo = Auth::checkAuth($token);
        Password::edit($userinfo['userid'], $oldPwd, $newPwd);
        $this->render(STATUS_SUCCESS, '密码修改成功');
    }
}