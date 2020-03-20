<?php
/**
 * 广告列表接口。
 * 
 * @author fingerQin
 * @date 2018-08-20
 * @version 1.0.0
 */

namespace Apis\app\v100\System;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\System\Advertisement;

class SystemAdsApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token    = $this->getString('token', '');
        $code     = $this->getString('place_code');
        $appV     = $this->getString('app_v', '');
        $platform = $this->getInt('platform', 0);
        $userid   = Auth::getTokenUserId($token);
        $result   = Advertisement::list($code, $appV, $userid, $platform);
        $this->render(STATUS_SUCCESS, 'success', ['list' => $result]);
    }
}