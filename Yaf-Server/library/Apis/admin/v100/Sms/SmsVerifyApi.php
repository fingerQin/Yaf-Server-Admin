<?php
/**
 * 短信验证接口。
 * @author fingerQin
 * @date 2018-07-30
 */

namespace Apis\admin\v100\Sms;

use finger\Utils\YCore;
use Apis\AbstractApi;
use Services\Sms\Sms;

class SmsVerifyApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $this->isAllowAccessApi(0);
        $mobile = $this->getString('mobile', '');
        $key    = $this->getString('key', '');
        $code   = $this->getString('code', '');
        $ip     = YCore::ip();
        $result = Sms::verify($mobile, $code, $key, $isDestroy = 0, $ip);
        $this->render(STATUS_SUCCESS, '验证码正确', $result);
    }
}
