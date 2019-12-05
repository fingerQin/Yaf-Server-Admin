<?php
/**
 * 访问禁止业务封装。
 * @author fingerQin
 * @date 2019-06-05
 */

namespace Services\AccessForbid;

use finger\Lock;
use Models\ForbidIp;
use finger\Utils\YCache;
use finger\Utils\YCore;

class Forbid extends \Services\AbstractBase
{
    /**
     * 埋点位置常量。
     */
    const POSITION_LOGIN    = 'login';      // 登录失败次数：疑似撞库。
    const POSITION_REGISTER = 'register';   // 注册提交次数：疑似批量注册垃圾账号。
    const POSITION_FIND_PWD = 'findPwd';    // 找回密码提交次数：疑似批量爆破或给正常用户造成安全困扰。
    const POSITION_SMS_SEND = 'sms_send';   // 短信发送次数：疑似资源恶意消耗。

    /**
     * 位置字典。
     *
     * @var array
     */
    public static $positionDict = [
        self::POSITION_LOGIN,
        self::POSITION_REGISTER,
        self::POSITION_FIND_PWD,
        self::POSITION_SMS_SEND
    ];

    /**
     * IP 触发封禁条件之 Redis KEY 前缀。
     */
    const IP_LOCK_KEY = 'system_forbid_ip_lock';

    /**
     * IP 风险位置埋点。
     *
     * @param  string  $position  位置编码。
     * @param  int     $times     风险触发次数。
     * @param  int     $lockTime  锁定时长(分钟)。
     *
     * @return void
     */
    public static function position($position, $times, $lockTime = 30)
    {
        $date     = date('Ymd', TIMESTAMP);
        $cacheKey = "system_forbin_ip:{$position}_{$date}";
        $redis    = YCache::getRedisClient();
        $int      = $redis->incr($cacheKey);
        if ($int == 1) {
            $redis->expire($cacheKey, $lockTime * 60);
        } elseif ($int >= $times) {
            $lockTsp  = TIMESTAMP + $lockTime * 60;     // 失效时间戳。
            $deadline = date('Y-m-d H:i:s', $lockTsp);  // 限制结束时间。
            $redis->set(self::IP_LOCK_KEY . ":{$date}", $deadline, ['NX', 'EX' => $lockTime * 60]);
        }
    }

    /**
     * IP 访问权限判断。
     *
     * -- 如果检测到属于风险 IP，则提示是否允许继承访问。
     * 
     * @param  string  $ip  用户 ID。
     *
     * @return void
     */
    public static function check($ip)
    {
        $date     = date('Ymd', TIMESTAMP);
        $cacheKey = self::IP_LOCK_KEY . ":{$date}";
        $redis    = YCache::getRedisClient();
        $lockTime = $redis->get($cacheKey);
        if ($lockTime != FALSE && $lockTime > date('Y-m-d H:i:s', TIMESTAMP)) {
            YCore::exception(STATUS_ACCESS_FORBID_IP, "您所在网络被禁止访问，解封时间：{$lockTime}");
        }
        $ips = self::getAllIp();
        if (array_key_exists($ip, $ips)) {
            YCore::exception(STATUS_ACCESS_FORBID_IP, "您所在网络被禁止访问，解封时间：{$ips[$ip]}");
        }
    }

    /**
     * 获取所有封禁的 IP 列表。
     *
     * @return array
     */
    private static function getAllIp()
    {
        $cacheKey = 'system_forbid_ip_list';
        $redis    = YCache::getRedisClient();
        $result   = $redis->get($cacheKey);
        if ($result !== false ) {
            return json_decode($result, true);
        } else {
            $data = self::getRealTimeDbIp();
            $redis->set($cacheKey, json_encode($data));
            return $data;
        }
    }

    /**
     * 实时获取数据库 IP 列表。
     *
     * @return array
     */
    private static function getRealTimeDbIp()
    {
        $lockKey = 'system_fobid_ip_lock';
        $status  = Lock::lock($lockKey, 3);
        if ($status) {
            $datetime = date('Y-m-d H:i:s', TIMESTAMP);
            $columns  = ['ip', 'deadline'];
            $where    = ['deadline' => ['<=', $datetime]];
            $result   = (new ForbidIp())->fetchAll($columns, $where);
            $data     = [];
            foreach ($result as $item) {
                $data[$item['ip']] = $item['deadline'];
            }
            unset($result);
            Lock::release($lockKey);
            return $data;
        } else {
            // 这一步看似无限递归。实则是为了锁定时间超时之后，重新再读取一次缓存的值。
            return self::getAllIp();
        }
    }
}