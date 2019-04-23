<?php
/**
 * 短信发送接口。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Apis\admin\v100\Sms;

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
        $mobile   = $this->getString('mobile', '');
        $key      = $this->getString('key', '');
        $platform = $this->getString('platform');
        $result   = Sms::send($mobile, $key, '', $platform);
        $this->render(STATUS_SUCCESS, '发送成功', $result);
    }
}
