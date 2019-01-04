<?php
/**
 * 短信发送接口。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Apis\app\v100\Sms;

use Utils\YCore;
use Apis\AbstractApi;
use Services\Sms\Sms;

class SmsSendApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $this->isAllowAccessApi(0);
        $mobile      = $this->getString('mobile', '');
        $key         = $this->getString('key', '');
        $platform    = $this->getString('platform');
        $channel     = $this->getString('channel', '');
        $deviceToken = $this->getString('device_token', '');
        $appV        = $this->getString('app_v', '');
        $ip          = YCore::ip();
        $result      = Sms::send($mobile, $key, $ip, $platform);
        $this->render(STATUS_SUCCESS, '发送成功', $result);
    }
}
