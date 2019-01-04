<?php
/**
 * 升级封装。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Services\System;

use Utils\YCore;
use Models\AppUpgrade;

class Upgrade extends \Services\AbstractBase
{
    /**
     * 允许的合法 APP 类型字典。
     * 
     * @var array
     */
    protected static $allowPlatformDict = [
        AppUpgrade::APP_TYPE_ANDROID => 'android',
        AppUpgrade::APP_TYPE_IOS     => 'ios'
    ];

    /**
     * APP 升级信息。
     *
     * @param  string  $userid    用户ID。
     * @param  int     $platform  APP类型。1 - IOS、2 - Android。
     * @param  string  $appV      APP 版本号。
     * @param  string  $channel   渠道。Android 专用。
     * @return array
     */
    public static function upgrade($userid, $platform, $appV, $channel = '')
    {
        if (!self::allowPlatform($platform)) {
            YCore::exception(STATUS_SERVER_ERROR, 'APP 类型有误');
        }
        $appinfo = self::getAppInfo($platform, $appV, $channel);
        if (empty($appinfo)) {
            $appinfo['upgrade_way'] = AppUpgrade::UPGRADE_WAY_NO;
        }
        switch (intval($appinfo['upgrade_way'])) {
            case AppUpgrade::UPGRADE_WAY_NO:
                return self::getAppReturn(AppUpgrade::UPGRADE_WAY_NO, '', '', '', '', 0, $appV);
                break;
            case AppUpgrade::UPGRADE_WAY_ADVISE:
                $new = self::getNewestVersion($appV, $platform, $channel);
                if (!empty($new)) {
                    return self::getAppReturn(AppUpgrade::UPGRADE_WAY_ADVISE, $new['app_title'], $new['app_v'], $new['app_desc'], $new['url'], $appinfo['dialog_repeat'], $appV);
                } else {
                    return self::getAppReturn(AppUpgrade::UPGRADE_WAY_NO, '', '', '', '', 0, $appV);
                }
                break;
            case AppUpgrade::UPGRADE_WAY_FORCE:
                $new = self::getNewestVersion($appV, $platform, $channel);
                if (!empty($new)) {
                    return self::getAppReturn(AppUpgrade::UPGRADE_WAY_FORCE, $new['app_title'], $new['app_v'], $new['app_desc'], $new['url'], $appinfo['dialog_repeat'], $appV);
                } else {
                    return self::getAppReturn(AppUpgrade::UPGRADE_WAY_NO, '', '', '', '', 0, $appV);
                }
                break;
            case AppUpgrade::UPGRADE_WAY_CLOSE:
                return self::getAppReturn(AppUpgrade::UPGRADE_WAY_CLOSE, '', '', '', '', 0, $appV);
                break;
            default:
                YCore::exception(STATUS_SERVER_ERROR, 'APP 升级类型异常');
                break;
        }
    }

    /**
     * 允许的 APP 类型。
     * 
     * @param  int  $platform  APP类型。1 - ios、2 - Android。
     * @return bool
     */
    private static function allowPlatform($platform)
    {
        if (array_key_exists($platform, self::$allowPlatformDict)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取指定版本信息。
     * 
     * @param  string  $platform  APP类型。1 - ios、2 - Android。
     * @param  string  $appV      APP 版本号。
     * @param  string  $channel   Android 渠道。
     * @return array
     */
    private static function getAppInfo($platform, $appV, $channel)
    {
        $where = [
            'app_type'   => $platform,
            'app_v'      => $appV,
            'app_status' => AppUpgrade::STATUS_YES
        ];
        // Android 则需要判断渠道。
        if ($platform == AppUpgrade::APP_TYPE_ANDROID) {
            $where['channel'] = $channel;
        }
        $columns = ['app_title', 'app_v', 'app_desc', 'url', 'upgrade_way', 'dialog_repeat'];
        $appinfo = (new AppUpgrade())->fetchOne($columns, $where);
        if (empty($appinfo)) {
            return []; // 未设置升级数据，代表不需要升级。
        } else {
            return $appinfo;
        }
    }

    /**
     * 组装升级接口返回的数据。
     * 
     * @param  int      $upgradeWay     升级模式。
     * @param  string   $appTitle       升级标题。
     * @param  string   $appV           升级版本。
     * @param  string   $appDesc        升级描述。
     * @param  string   $appUrl         APP下载URL地址。
     * @param  int      $dialogRepeat   建议升级时弹窗是否重复弹出。0 - 否，1 - 是。
     * @param  string   $originAppV     原版本(用户当前 APP 版本)。
     * @return array
     */
    private static function getAppReturn($upgradeWay, $appTitle, $appV, $appDesc, $appUrl, $dialogRepeat, $originAppV)
    {
        return [
            'upgrade_way'   => $upgradeWay,
            'app_v'         => $appV,
            'app_title'     => $appTitle,
            'app_desc'      => $appDesc,
            'app_url'       => $appUrl,
            'dialog_repeat' => $dialogRepeat,
            'origin_v'      => $originAppV
        ];
    }

    /**
     * 获取最新的 APP 版本。
     * 
     * @param  string  $appV      大于此版本。此版本是用户当前请求的版本。
     * @param  string  $platform  APP类型。1 - android、2 - ios。
     * @param  string  $channel   Android 渠道。
     * @return array
     */
    private static function getNewestVersion($appV, $platform, $channel)
    {
        $where = [
            'app_type'   => $platform,
            'app_status' => AppUpgrade::STATUS_YES,
            'app_v'      => ['>', $appV]
        ];
        // Android 必须设置渠道。
        if ($platform == AppUpgrade::APP_TYPE_ANDROID) {
            $where['channel'] = $channel;
        }
        $columns = ['app_title', 'app_v', 'app_desc', 'url', 'upgrade_way', 'dialog_repeat'];
        $orderBy = 'app_v DESC,id DESC';
        $appinfo = (new AppUpgrade())->fetchOne($columns, $where, $orderBy);
        if (empty($appinfo)) {
            return [];
        } else {
            return $appinfo;
        }
    }
}