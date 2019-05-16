<?php
/**
 * IP 类。
 * @author fingerQin
 * @date 2019-05-16
 */

namespace finger;

use Utils\YCore;

class Ip
{
    /**
     * 获取请求ip
     *
     * @return string ip 地址
     */
    public static function ip()
    {
        $ip = '127.0.0.1';
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
    }
 
    /**
     * 判断 IP 是否在一个 IP 段内
     *
     * @param  string  $startIp  开始IP
     * @param  string  $endIp    结束IP
     * @param  string  $ip       IP
     *
     * @return bool
     */
    public static function isRange($startIp, $endIp, $ip)
    {
        $start  = ip2long($startIp);
        $end    = ip2long($endIp);
        $ipInt  = ip2long($ip);
        $result = false;
        if ($ipInt >= $start && $ipInt <= $end) {
            $result = true;
        }
        return $result;
    }
}