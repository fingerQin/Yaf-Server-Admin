<?php
/**
 * 跟 APP 推送相关的业务封装。
 * 
 * @author fingerQin
 * @date 2018-06-29
 */

namespace Services\System;

use finger\Utils\YCore;
use finger\Utils\YCache;
use Models\PushDevice;

class Push extends \Services\AbstractBase
{
    /**
     * 推送消息到 Redis 频道。
     * 
     * @param  string  $msgType  消息类型。具体见当前类常量定义。
     * @param  array   $data     推送的数据。不同的消息有不同的格式。该格式由 Java 消费端定义。
     * @return void
     */
    protected static function send($msgType, $data)
    {
        $redis  = YCache::getRedisClient();
        $status = $redis->publish($msgType, json_encode($data, JSON_UNESCAPED_UNICODE));
        $status = intval($status);
        YCore::log(['status' => $status, 'msgType' => $msgType, 'data' => $data], 'push', 'log');
    }

    /**
     * 清除用户关联的信鸽 device_token。
     * 
     * @param  int  $userid  用户 ID。
     * @return void
     */
    public static function clearUserAssocDeviceToken($userid)
    {
        $MPushDevice = new PushDevice();
        $device = $MPushDevice->fetchOne([], ['userid' => $userid]);
        if (empty($device) || strlen($device['device_token']) === 0) { // 防止多次提交退出登录。
            return;
        }
        $data = [
            'device_token'      => '',
            'last_device_token' => $device['device_token'],
            'last_device_type'  => $device['device_type'],
        ];
        $MPushDevice->update($data, ['id' => $device['id']]);
    }

    /**
     * 注册用户关联的设备 TOKEN。
     * 
     * @param  int     $userid       用户 ID。
     * @param  string  $deviceToken  设备 TOKEN。
     * @param  int     $platform     平台。
     * @param  string  $appV         APP 版本号。
     * @return bool
     */
    public static function registerUserAssocDeviceToken($userid, $deviceToken, $platform, $appV)
    {
        if (!self::isAppCall($platform) || (strlen($appV) === 0)) {
            return true; // 非客户端或都 APP 版本号未传则直接返回不记录。
        }
        $MPushDevice = new PushDevice();
        $device      = $MPushDevice->fetchOne([], ['userid' => $userid]);
        if (empty($device)) {
            $data = [
                'userid'       => $userid,
                'device_token' => $deviceToken,
                'device_type'  => $platform,
                'app_ver'      => $appV
            ];
            return $MPushDevice->insert($data);
        } else {
            $data = [
                'last_device_token' => $device['device_token'],
                'last_device_type'  => $device['device_type'],
                'device_token'      => $deviceToken,
                'device_type'       => $platform,
                'app_ver'           => $appV
            ];
            return $MPushDevice->update($data, ['id' => $device['id']]);
        }
    }
}