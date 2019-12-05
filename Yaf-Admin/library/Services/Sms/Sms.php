<?php
/**
 * 短信操作封装。
 * -- 调用 Yaf-Server 接口实现。
 * @author fingerQin
 * @date 2018-07-06
 */

namespace Services\Sms;

use finger\Utils\YCore;
use ApiTools\Request;
use Models\AdminUser;

class Sms extends \Services\AbstractBase
{
    /**
     * 发送管理员登录短信。
     *
     * @param  string  $mobile  手机号。
     *
     * @return void
     */
    public static function sendAdminLogin($mobile)
    {
        $AdminUserModel = new AdminUser();
        $adminDetail    = $AdminUserModel->fetchOne([], [
            'mobile'      => $mobile, 
            'user_status' => AdminUser::STATUS_YES
        ]);
        if (empty($adminDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '账号不存在');
        }
        self::send($mobile, 'ADMIN_LOGIN_CODE');
    }

    /**
     * 发送短信(用户类型短信)。
     * 
     * @param  string  $mobile   手机号
     * @param  string  $smsType  模板标识
     *
     * @return void
     */
    public static function send($mobile, $smsType)
    {
        $params = [
            'method'   => 'sms.send',
            'mobile'   => $mobile,
            'key'      => $smsType,
            'platform' => self::PLATFORM_ADMIN
        ];
        $result = (new Request())->send($params);
        if ($result['code'] != STATUS_SUCCESS) {
            YCore::exception($result['code'], $result['msg']);
        }
    }

    /**
     * 验证码验证。
     *
     * @param  string  $mobile   手机号码。
     * @param  string  $smsType  短信类型。
     * @param  string  $code     验证码。
     *
     * @return void
     */
    public static function verify($mobile, $smsType, $code)
    {
        $params = [
            'method'   => 'sms.verify',
            'mobile'   => $mobile,
            'key'      => $smsType,
            'code'     => $code,
            'platform' => self::PLATFORM_ADMIN
        ];
        $result = (new Request())->send($params);
        if ($result['code'] != STATUS_SUCCESS) {
            YCore::exception($result['code'], $result['msg']);
        }
    }

    /**
     * 系统触发的短信。
     * 
     * -- 系统通知类型的短信。
     *
     * @param  string  $mobile      手机号
     * @param  string  $smsType     模板标识
     * @param  array   $replaceArr  模板中待转转换的值。
     *
     * @return void
     */
    public static function sendSystem($mobile, $smsType, $replaceArr = [])
    {
        $params = [
            'method'   => 'sms.send.system',
            'mobile'   => $mobile,
            'key'      => $smsType,
            'platform' => self::PLATFORM_ADMIN,
            'replace'  => $replaceArr
        ];
        $result = (new Request())->send($params);
        if ($result['code'] != STATUS_SUCCESS) {
            YCore::exception($result['code'], $result['msg']);
        }
    }
}