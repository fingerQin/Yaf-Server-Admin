<?php
/**
 * APP 初始化。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Services\System;

use Services\AbstractBase;

class AppInit extends AbstractBase
{
    /**
     * 启动 APP 初始化。
     * 
     * --1) 会话 TOKEN 刷新。
     * --2) APP 升级信息。
     * --3) 开屏广告。
     * --4) 弹窗公告。
     * --5) 其他事先需要转入的配置信息。
     *
     * @param  string  $token     会话 TOKEN。
     * @param  int     $platform  平台：1-IOS、2-Android、3-H5、4-WEB
     * @param  string  $channel   渠道。IOS 没有渠道。只有 Android 才会有。
     * @param  string  $appV      APP 版本号。 eg:1.0.0
     * @return void
     */
    public static function launch($token, $platform, $channel, $appV)
    {
        $tokenStatus = \Services\User\Auth::refreshToken($token);
        $userid      = \Services\User\Auth::getTokenUserId($token);
        return [
            'token_status' => $tokenStatus ? 1 : 0,
            'upgrade'      => \Services\System\Upgrade::upgrade($userid, $platform, $appV, $channel),
        ];
    }
}