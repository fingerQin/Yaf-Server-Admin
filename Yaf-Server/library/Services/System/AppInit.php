<?php
/**
 * APP 初始化。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Services\System;

use Utils\YCore;
use Services\AbstractBase;
use Services\System\Upgrade;
use Services\System\Advertisement;
use Services\System\Config;
use Services\User\Auth;

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
        $tokenStatus = Auth::refreshToken($token);
        $userid      = Auth::getTokenUserId($token);
        return [
            'token_status'           => $tokenStatus ? 1 : 0, // TOKEN 有效无效。
            'upgrade'                => Upgrade::upgrade($userid, $platform, $appV, $channel),
            'start_ad'               => Advertisement::single('APP_START_AD', $appV, $userid, $platform),
            'app_home_right_btn_url' => Config::get('APP_HOME_RIGHT_BTN_URL'),
            'app_service'            => self::getAppLeftBottomBtn('服务中心', 'APP_SERVICE_CONFIG'),
            'app_about'              => self::getAppLeftBottomBtn('关于我们', 'APP_ABOUT_CONFIG'),
            'app_feedback'           => self::getAppLeftBottomBtn('意见反馈', 'APP_FEEDBACK_CONFIG'),
            'notice_dialog'          => Notice::appDialog()
        ];
    }

    /**
     * 读取 APP 左侧滑动菜单底部按钮配置。
     *
     * @param  string  $cfgName  按钮名称。用于提示按钮配置读取失败的提示。
     * @param  string  $cfgKey   按钮配置 KEY。
     *
     * @return array
     */
    private static function getAppLeftBottomBtn($cfgName, $cfgKey)
    {
        $appServiceCfg = Config::get($cfgKey);
        if (empty($appServiceCfg)) {
            YCore::exception(STATUS_SERVER_ERROR, '服务中心配置读取失败');
        }
        $cfg = explode(':::', $appServiceCfg);
        if (count($cfg) != 2) {
            YCore::exception(STATUS_SERVER_ERROR, "{$cfgName}配置设置错误");
        }
        return [
            'txt' => $cfg[0],
            'url' => $cfg[1]
        ];
    }
}