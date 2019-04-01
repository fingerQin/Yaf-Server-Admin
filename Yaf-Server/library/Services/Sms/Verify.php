<?php
/**
 * 短信相关限制判断(不是验证码验证)。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Services\Sms;

use Utils\YCore;
use Utils\YCache;
use Models\SmsBlacklist;
use Models\SmsSendLog;
use Models\SmsTpl;
use finger\Database\Db;

class Verify extends \Services\Sms\AbstractBase
{
    /**
     * 检测两次发送的间隔(用户触发的才需要调用)。
     * 
     * @param  string  $mobile  手机号码。
     * @return bool true - 不受时间间隔限制、false - 受时间间隔所限。
     */
    public static function checkSendInterval($mobile)
    {
        $interval = YCore::appconfig('sms.interval');
        $cacheKey = "sms_interval:{$mobile}";
        $sendTsp  = YCache::get($cacheKey);
        $time     = time();
        if ($sendTsp > 0 && (($time - $sendTsp) < $interval)) {
            YCore::exception(STATUS_SERVER_ERROR, "两次发送间隔不能小于{$interval}秒!");
        } else {
            YCache::set($cacheKey, $time, $interval); // 记录最后一次发送的时间。
            return true;
        }
    }

    /**
     * 检测当日 IP 发送是否超限。
     * 
     * @param  string  $ip
     * @return void
     */
    public static function checkDayIpTimes($ip)
    {
        $ipSendmax = YCore::appconfig('sms.ip_sendmax');
        if ($ipSendmax > 0) {
            $datetime = date('Y-m-d 00:00:00', time());
            $sql = 'SELECT COUNT(1) AS count FROM finger_sms_sendlog AS a '
                 . 'INNER JOIN finger_sms_tpl AS b ON(a.tpl_id=b.id) '
                 . 'WHERE a.ip = :ip AND a.c_time > :c_time AND b.trigger_type = :trigger_type';
            $params = [
                ':ip'           => $ip,
                ':c_time'       => $datetime,
                ':trigger_type' => SmsTpl::TRIGGER_TYPE_USER
            ];
            $result = Db::one($sql, $params);
            $count  = $result ? $result['count'] : 0;
            if ($count >= $ipSendmax) {
                YCore::exception(STATUS_SERVER_ERROR, "您的IP今日发送超过上限!");
            }
        }
        return true;
    }

    /**
     * 检测当日手机号发送次数是否超限。
     * 
     * @param  string  $mobile  手机号码。
     * @return bool true - 未超限可发、false - 已超限不可发。
     */
    public static function checkDayMobileTimes($mobile)
    {
        $mobileSendmax = YCore::appconfig('sms.mobile_sendmax');
        if ($mobileSendmax > 0) {
            $datetime = date('Y-m-d 00:00:00', time());
            $where    = [
                'mobile' => $mobile,
                'c_time' => ['>', $datetime]
            ];
            $count = (new SmsSendLog())->count($where);
            if ($count >= $mobileSendmax) {
                YCore::exception(STATUS_SERVER_ERROR, "您的号码今天已发送超过{$mobileSendmax}次!");
            }
        }
        return true;
    }

    /**
     * 是否为内部手机号码。
     * 
     * -- 内部手机号码不受各种限制。
     *
     * @param  string  $mobile  手机号码。
     * @return bool true-内部手机号、false-不是内部手机号。
     */
    public static function isInsideMobile($mobile)
    {
        $mobileWhiteList = YCore::appconfig('sms.inside_mobiles');
        if (strlen($mobileWhiteList) === 0) {
            return false;
        }
        $mobileWhiteList = explode(',', $mobileWhiteList);
        if (in_array($mobile, $mobileWhiteList)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 是否为黑名单手机号。
     * 
     * @param  string  $mobile  手机号码。
     * @return bool true-在黑名单、false-不在黑名单。
     */
    public static function isBlacklistMobile($mobile)
    {
        $result = (new SmsBlacklist())->fetchOne([], ['mobile' => $mobile]);
        return $result ? true : false;
    }
}