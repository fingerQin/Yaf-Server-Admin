<?php
/**
 * 系统请求相关验证。
 * -- 系统并不允许未经登录就允许提交任何信息。此操作能最大防止系统被恶意信息破坏。
 * @author fingerQin
 * @date 2018-08-06
 */

namespace Services\System;

use finger\App;
use finger\Cache;
use finger\Core;
use finger\Validator;
use Services\AbstractBase;

class Request extends AbstractBase
{
    /**
     * 获取请求令牌(用于防重复/CSRF)。
     *
     * @param  int     $userid      用户 ID。
     * @param  string  $number      令牌数量。
     * @param  int     $expireTime  令牌有效期。超过这个时间必须重新操作。
     * @return string
     */
    public static function token($userid, $number = 1, $expireTime = 1800)
    {
        if (!Validator::is_number_between($number, 1, 5)) {
            Core::exception(STATUS_SERVER_ERROR, '令牌数量必须1~5之间');
        }
        $tokens = [];
        for ($i = 1; $i <= $number; $i++) {
            $key   = "R-U-Token:{$userid}{$i}";
            $token = self::createToken();
            Cache::set($key, $token, $expireTime);
            $tokens["{$i}"] = "{$i}:{$token}";
        }
        return $tokens;
    }

    /**
     * 验证请求令牌(用于防重复/CSRF)。
     *
     * @param  int     $userid  用户 ID。
     * @param  string  $token   请求令牌。
     * @return void
     */
    public static function verify($userid, $token)
    {
        // 开发环境不做请求 TOKEN 验证。
        if (App::getConfig('app.env') == ENV_DEV) {
            return;
        }
        $params = explode(':', $token);
        if (count($params) != 2) {
            Core::exception(STATUS_SERVER_ERROR, '服务器发生一个错误,请退出重试!');
        }
        list($index, $token) = $params;
        $key    = "R-U-Token:{$userid}{$index}";
        $cToken = Cache::get($key);
        if (!$cToken) {
            Core::exception(STATUS_SERVER_ERROR, '您的操作已经过期!请退出重新操作!');
        }
        if ($cToken != $token) {
            Core::exception(STATUS_SERVER_ERROR, '您的提交出现异常!请退出重新操作!');
        }
        Cache::delete($key);
    }

    /**
     * 获取 TOKEN 值。
     *
     * @return int
     */
    private static function createToken()
    {
        $redis   = Cache::getRedisClient();
        $incrVal = $redis->incr('R-Token-UniqueId');
        return md5(App::getConfig('app.key') . $incrVal);
    }
}