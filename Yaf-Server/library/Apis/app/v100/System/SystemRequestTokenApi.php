<?php
/**
 * 获取请求令牌(防重复提交令牌)。
 * @author fingerQin
 * @date 2018-08-06
 */

namespace Apis\app\v100\System;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\System\Request;

class SystemRequestTokenApi extends AbstractApi
{
    /**
     * 逻辑处理。
     *
     * @return void
     */
    public function runService()
    {
        $token    = $this->getString('token', '');
        $number   = $this->getInt('number', 1);
        $userinfo = Auth::checkAuth($token);
        $reqToken = Request::token($userinfo['userid'], $number);
        $this->render(STATUS_SUCCESS, 'success', $reqToken);
    }
}