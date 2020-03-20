<?php
/**
 * 用户注册接口。
 * @author fingerQin
 * @date 2018-06-29
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;

class UserRegisterApi extends AbstractApi
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
        $code        = $this->getString('sms_code', '');
        $password    = $this->getString('password', '');
        $platform    = $this->getString('platform', 0);
        $channel     = $this->getString('channel', '');
        $appV        = $this->getString('app_v', '');
        $deviceToken = $this->getString('device_token', '');
        $v           = $this->getString('v');
        $activityId  = $this->getString('activity_id', '');
        $inviteUser  = $this->getString('invite_user', '');
        $result      = Auth::register($mobile, $code, $password, $platform, $channel, $appV, $deviceToken, $v, $activityId, $inviteUser);
        $this->render(STATUS_SUCCESS, '注册成功', $result);
    }
}