<?php
/**
 * 短信发送接口。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Apis\activity\v100\Sms;

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
        $mobile      = $this->getString('mobile', '');
        $key         = $this->getString('key', '');
        $platform    = $this->getString('platform');
        $channel     = $this->getString('channel', '');
        $deviceToken = $this->getString('device_token', '');
        $appV        = $this->getString('app_v', '');
        $result      = Sms::send($mobile, $key, '', $platform);
        $this->render(200, '发送成功', $result);
    }
}
