<?php
/**
 * 短信发送封装。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Services\Sms;

use finger\Validator;
use Utils\YCore;
use Utils\YLog;
use Utils\YCache;
use Models\SmsTpl;
use Models\SmsSendLog;

class Sms extends \Services\Sms\AbstractBase
{
    /**
     * 发送短信(用户类型短信)。
     * 
     * @param  string  $mobile      手机号
     * @param  string  $smsType     模板标识
     * @param  string  $sendIp      ip 地址
     * @param  string  $platform    平台。1-ios、2-android、3-wap、4-PC。
     * @param  string  $replaceArr  替换模板数组。一般不需要传，需传特殊值可用此参数。['PHOME' => 'xxx', 'USERNAME' => 'xxx']
     * -- $replaceArr 此数组最好不要使用键名 [code]，防止混淆。
     *
     * @return void
     */
    public static function send($mobile, $smsType, $sendIp = '', $platform, $replaceArr = [])
    {
        // [1]
        $sendIp = (strlen($sendIp) > 0) ? $sendIp : YCore::ip();
        YLog::log(['sendIp' => $sendIp, 'smsType' => $smsType, 'mobile' => $mobile], 'sms', 'sendLog');
        if (!Validator::is_mobilephone($mobile)) {
            YCore::exception(STATUS_SERVER_ERROR, '手机号码不正确!');
        }
        // [2] 内部手机号码不做发送限制。
        $isInsideMobile = Verify::isInsideMobile($mobile);
        if (!$isInsideMobile) {
            Verify::checkDayMobileTimes($mobile);
            Verify::checkDayIpTimes($sendIp);
            Verify::checkSendInterval($mobile);
            if (Verify::isBlacklistMobile($mobile)) {
                YCore::exception(STATUS_SERVER_ERROR, '您的手机号码暂时无法接收短信');
            }
        }
        // [3] 开发机或内部手机号码验证码为 123456.
        $code = rand(100000, 999999);
        if (YCore::appconfig('app.env') == 'dev' || $isInsideMobile) {
            $code = 123456;
        }
        // 获取发送模板
        $result = self::getReplaceContent($smsType, $mobile, $code, $replaceArr);
        // 创建短信日志
        $MSmsSendLog = new SmsSendLog();
        $datetime    = date('Y-m-d H:i:s', time());
        $data = [
            'mobile'      => $mobile,
            'content'     => $result['content'],
            'tpl_id'      => $result['tpl_id'],
            'verify_code' => $code,
            'ip'          => $sendIp,
            'c_time'      => $datetime,
            's_time'      => $datetime,
            'sms_status'  => SmsSendLog::SEND_STATUS_CREATE,
            'sms_type'    => SmsSendLog::SMS_TYPE_TXT,
            'platform'    => $platform
        ];
        $id = $MSmsSendLog->insert($data);
        if (!$id) {
            YLog::log($data, 'sms', 'error');
            YCore::exception(STATUS_SERVER_ERROR, '短信发送失败');
        }
        $queueData = [
            'mobile'  => $mobile,
            'content' => $result['content'],
            'id'      => $id
        ];
        if (!$isInsideMobile && YCore::appconfig('app.env')) { // 内部手机号码不发送短信。
            self::clearVerifyTimesKey($mobile, $smsType);
            Queue::pushSmsQueue($queueData);
        }
    }

    /**
     * 系统触发的短信。
     * 
     * -- 系统通知类型的短信。
     * 
     * @param  string  $mobile      手机号
     * @param  string  $smsType     模板标识
     * @param  string  $sendIp      ip 地址
	 * @param  string  $platform    平台。1-ios、2-android、3-wap、4-PC。
     * @param  string  $replaceArr  短信模板中待转换的参数。
     */
    public static function sendSystem($mobile, $smsType, $sendIp = '', $platform, $replaceArr = [])
    {
        $templet   = self::getTemplet($smsType);
        $sendIp    = (strlen($sendIp) > 0) ? $sendIp : YCore::ip();
        YLog::log(['sendIp' => $sendIp, 'smsType' => $smsType, 'mobile' => $mobile], 'sms', 'sendSystemLog');
        $result    = self::getReplaceContent($smsType, $mobile, '', $replaceArr);
        // 创建短信日志。
        $datetime    = date('Y-m-d H:i:s', time());
        $MSmsSendLog = new SmsSendLog();
        $data = [
            'mobile'     => $mobile,
            'content'    => $result['content'],
            'tpl_id'     => $result['tpl_id'],
            'ip'         => $sendIp,
            'c_time'     => $datetime,
            'sms_status' => SmsSendLog::SEND_STATUS_CREATE,
            'sms_type'   => SmsSendLog::SMS_TYPE_TXT,
            'platform'   => $platform
        ];
        $id = $MSmsSendLog->insert($data);
        if (!$id) {
            YLog::log($data, 'sms', 'error');
            YCore::exception(STATUS_SERVER_ERROR, '短信发送失败');
        }
        // 短信入队列服务
        $queueData = [
            'mobile'  => $mobile,
            'content' => $result['content'],
            'id'      => $id
        ];
        Queue::pushSmsQueue($queueData);
    }

    /**
     * 短信模板替换。
     *
     * @param  string  $smsType     关键字
     * @param  string  $mobile      手机号
     * @param  string  $code        验证码/免密自动注册时为密码。
     * @param  array   $replaceArr  扩展短信字段
     * 
     * -- eg:start --
     * $replaceArr = ['username' => 'fingerQin', 'sex' => '覃先生']
     * 将替换模板中的 %USERNAME% 为 fingerQin, %sex% 替换为 覃先生。
     * -- eg:end --
     * 
     * @return []
     */
    private static function getReplaceContent($smsType, $mobile, $code = '', $replaceArr = [])
    {
        $templet = self::getTemplet($smsType);
        $keys    = ['%CODE%'];
        $values  = [$code];
        if (!empty($replaceArr)) {
            $arr = array_change_key_case($replaceArr, CASE_UPPER); // 键名转大写
            foreach ($arr as $k => $item){
                array_push($keys, '%'.$k.'%');
                array_push($values, $item);
            }
        }
        // 短信内容替换
        $content = str_replace($keys, $values, $templet['content']);
        return ['tpl_id' => $templet['id'], 'content' => $content];
    }

    /**
     * 验证短信验证码
     * 
     * @param  string  $mobile     手机号码
     * @param  string  $code       验证码
     * @param  int     $smsType    短信模板对应的标识
     * @param  int     $isDestroy  是否销毁 1 - 销毁 0 - 不销毁。
     * @param  string  $ip         IP 地址。用于检测两次 IP 位置是否异常。
     * 
     * @return void
     */
    public static function verify($mobile, $code, $smsType, $isDestroy = 1, $ip = '')
    {
        if (empty($code)) {
            YCore::exception(STATUS_SERVER_ERROR, '短信验证码不能为空');
        }
        if (empty($smsType)) {
            YCore::exception(STATUS_SERVER_ERROR, '短信模板标识不能为空');
        }
        $ip          = strlen($ip) > 0 ? $ip : YCore::ip();
        $templet     = self::getTemplet($smsType);
        $MSmsSendLog = new SmsSendLog();
        $smsLog      = $MSmsSendLog->fetchOne([], ['tpl_id' => $templet['id'], 'mobile' => $mobile], 'id DESC');
        if (empty($smsLog)) {
            YCore::exception(STATUS_SERVER_ERROR, '您的验证码不正确');
        }
        $expireTime = strtotime('+10minutes', strtotime($smsLog['c_time']));
        if (($smsLog['cksms'] == SmsSendLog::STATUS_USED) && (strtotime('now') > $expireTime)) {
            YCore::exception(STATUS_SERVER_ERROR, '请重新获取验证码');
        }
        if ($smsLog['cksms'] == SmsSendLog::STATUS_USED) {
            YCore::exception(STATUS_SERVER_ERROR, '验证码已使用');
        }
        if ($smsLog['cksms'] == SmsSendLog::STATUS_INVALID) {
            YCore::exception(STATUS_SERVER_ERROR, '验证码已失效');
        }
        if (strtotime('now') > $expireTime) {
            YCore::exception(STATUS_SERVER_ERROR, '您的验证码已失效,请重新获取!');
        }
        if ($ip != $smsLog['ip']) {
            YCore::exception(STATUS_SERVER_ERROR, '您的 IP 位置异常');
        }
        if ($smsLog['verify_code'] != $code) {
            $status = self::verifyTimes($mobile, $smsType);
            if (!$status) {
                $MSmsSendLog->update(['cksms' => SmsSendLog::STATUS_INVALID], ['id' => $smsLog['id']]);
            }
            YCore::exception(STATUS_SERVER_ERROR, '您的验证码不正确');
        }
        if ($isDestroy) {
            $MSmsSendLog->update(['cksms' => SmsSendLog::STATUS_USED], ['id' => $smsLog['id']]);
        }
        self::clearVerifyTimesKey($mobile, $smsType);
    }

    /**
     * 短信验证码验证次数判断。
     * 
     * @param  string  $mobile   手机号。
     * @param  string  $smsType  短信类型。
     * @return bool true-未受限、false-受限。
     */
    private static function verifyTimes($mobile, $smsType)
    {
        $verifyTimes = YCore::appconfig('sms.verify_times');
        $cacheKey    = "sms:{$smsType}-times:{$mobile}";
        $times       = YCache::get($cacheKey);
        if ($times > 0 && $times >= $verifyTimes) {
            self::clearVerifyTimesKey($mobile, $smsType);
            return false;
        } else {
            if ($times) { // 存在则自增1
                YCache::incr($cacheKey, 1);
            } else {
                YCache::set($cacheKey, 2, 60);
            }
            return true;
        }
    }

    /**
     * 短信验证码验证成功清除计数器缓存 KEY。
     * 
     * @param  string  $mobile   手机号。
     * @param  string  $smsType  短信类型。
     * @return void
     */
    private static function clearVerifyTimesKey($mobile, $smsType)
    {
        $cacheKey = "sms:{$smsType}-times:{$mobile}";
        YCache::delete($cacheKey);
    }

    /**
     * 获取发送模板
     * 
     * @param  string  $smsType  短信模板标识
     * @return array
     */
    public static function getTemplet($smsType)
    {
        $result = (new SmsTpl())->fetchOne([], ['send_key' => $smsType]);
        if (empty($result)) {
            YCore::exception(STATUS_SERVER_ERROR, '短信模板不存在');
        }
        return ['content' => $result['sms_body'], 'id' => $result['id'], 'send_key' => $result['send_key']];
    }
}