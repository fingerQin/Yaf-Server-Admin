<?php
/**
 * 发送系统短信接口。
 * @author fingerQin
 * @date 2018-07-30
 */

namespace Apis\admin\v100\Sms;

use Apis\AbstractApi;
use Services\Sms\Sms;

class SmsSendSystemApi extends AbstractApi
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
        $replace  = $this->getArray('replace', []);
        $result   = Sms::sendSystem($mobile, $key, '', $platform, $replace);
        $this->render(STATUS_SUCCESS, '发送成功', $result);
    }
}
