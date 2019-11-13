<?php
/**
 * 系统初始化接口。 
 * 
 * @author fingerQin
 * @date 2018-06-23
 * @version 1.0.0
 */

namespace Apis\app\v100\System;

use Apis\AbstractApi;
use Services\System\AppInit;

class SystemInitApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token    = $this->getString('token', '');
        $platform = $this->getString('platform');
        $channel  = $this->getString('channel', '');
        $appV     = $this->getString('app_v', '');
        $result   = AppInit::launch($token, $platform, $channel, $appV);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}