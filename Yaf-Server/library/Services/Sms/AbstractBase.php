<?php
/**
 * 短信模块服务基类。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Services\Sms;

abstract class AbstractBase extends \Services\AbstractBase
{
    /**
     * 短信队列KEY。
     */
    const SMS_QUEUE_KEY = 'sms-squeue';

    /**
     * 短信模板常量。
     */
    const SMS_TYPE_LOGIN    = 'USER_LOGIN_CODE';      // 登录模板。
    const SMS_TYPE_REGISTER = 'USER_REGISTER_CODE';   // 注册模板。
}