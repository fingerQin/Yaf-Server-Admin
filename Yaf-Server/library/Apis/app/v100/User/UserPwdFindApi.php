<?php
/**
 * 用户密码找回接口。
 * 
 * @author fingerQin
 * @date 2018-08-15
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Password;

class UserPwdFindApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $mobile   = $this->getString('mobile', '');
        $code     = $this->getString('sms_code', '');
        $password = $this->getstring('password', '');
        Password::find($mobile, $code, $password);
        $this->render(STATUS_SUCCESS, '密码找回成功');
    }
}