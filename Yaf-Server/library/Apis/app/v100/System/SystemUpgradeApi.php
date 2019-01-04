<?php
/**
 * APP 升级接口。
 * 
 * @author fingerQin
 * @date 2018-06-24
 * @version 1.0.0
 */

namespace Apis\app\v100\System;

use Utils\YCore;
use Apis\AbstractApi;
use Services\System\Upgrade;

class SystemUpgradeApi extends AbstractApi
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
        $result   = Upgrade::upgrade($token, $platform, $appV, $channel);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}