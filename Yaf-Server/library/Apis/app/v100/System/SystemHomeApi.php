<?php
/**
 * APP 首页接口。
 * 
 * @author fingerQin
 * @date 2018-08-31
 * @version 1.0.0
 */

namespace Apis\app\v100\System;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\System\Advertisement;

class SystemHomeApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token    = $this->getString('token', '');
        $appV     = $this->getString('app_v', '');
        $platform = $this->getInt('platform', 0);
        $userid   = Auth::getTokenUserId($token);
        $ads      = Advertisement::list('app_home', $appV, $userid, $platform);
        $this->render(STATUS_SUCCESS, 'success', ['ads' => $ads]);
    }
}