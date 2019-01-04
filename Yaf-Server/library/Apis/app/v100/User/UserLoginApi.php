<?php
/**
 * 用户登录接口。
 * @author fingerQin
 * @date 2018-06-27
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;

class UserLoginApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @see Api::runService()
     * @return void
     */
    protected function runService()
    {
        $mobile      = $this->getString('mobile');
        $loginType   = $this->getInt('login_type');
        $code        = $this->getString('code');
        $deviceToken = $this->getString('device_token', '');
        $platform    = $this->getString('platform', 0);
        $appV        = $this->getString('app_v', '');
        $v           = $this->getString('v');
        $result      = Auth::login($mobile, $loginType, $code, $platform, $appV, $deviceToken, $v);
        $this->render(STATUS_SUCCESS, '登录成功', $result);
    }
}