<?php
/**
 * 用户昵称修改接口。
 * 
 * @author fingerQin
 * @date 2020-03-19
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\User\User;

class UserNicknameEditApi extends AbstractApi
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
        $nickname = $this->getString('nickname', '');
        User::setNickname($userinfo['userid'], $nickname);
        $this->render(STATUS_SUCCESS, '修改成功', ['nickname' => $nickname]);
    }
}