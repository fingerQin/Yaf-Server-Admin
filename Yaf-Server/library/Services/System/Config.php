<?php
/**
 * 系统配置表数据读取。
 * @author fingerQin
 * @date 2019-05-02
 */

namespace Services\System;

use finger\Cache;
use finger\Core;
use Models\Config as ConfigModel;

class Config extends \Services\AbstractBase
{
    /**
     * 配置文件缓存 KEY。
     * -- 与管理后台对应。
     */
    const CONFIG_CACHE_KEY = 'system-configs';

    /**
     * 读取配置。
     *
     * @param  string  $cfgKey  配置名称。
     *
     * @return string
     */
    public static function get($cfgKey)
    {
        $cfgVal = self::readRequestCfgCache($cfgKey);
        if (!is_null($cfgVal)) {
            return $cfgVal;
        } else {
            return self::readDbConfig($cfgKey);
        }
    }

    /**
     * 读取数据库配置。
     *
     * @param  string  $cfgKey  配置名称。
     *
     * @return string
     */
    private static function readDbConfig($cfgKey)
    {
        $cfgVal = self::readRedisConfig($cfgKey);
        if (!is_null($cfgVal)) {
            return $cfgVal;
        }
        $ConfigModel  = new ConfigModel();
        $configs      = $ConfigModel->fetchAll(['cfg_key', 'cfg_value']);
        $recomConfigs = [];
        foreach ($configs as $config) {
            $recomConfigs[$config['cfg_key']] = $config['cfg_value'];
        }
        Cache::set(self::CONFIG_CACHE_KEY, json_encode($recomConfigs, JSON_UNESCAPED_UNICODE));
        if (isset($recomConfigs[$cfgKey])) {
            return $recomConfigs[$cfgKey];
        } else {
            Core::exception(STATUS_SERVER_ERROR, '系统配置出错,请联系客服');
        }
    }

    /**
     * 读取 Redis 配置。
     *
     * @param  string  $cfgKey  配置名称。
     *
     * @return string
     */
    private static function readRedisConfig($cfgKey)
    {
        $configs = Cache::get(self::CONFIG_CACHE_KEY);
        if ($configs === false) {
            return null;
        } else {
            $configs = json_decode($configs, true);
            return $configs[$cfgKey] ?? null;
        }
    }

    /**
     * 读取当前请求中缓存的配置信息。
     *
     * @param  string  $cfgKey  配置名称。
     *
     * @return string|null
     */
    private static function readRequestCfgCache($cfgKey)
    {
        $configs  = \Yaf_Registry::get(self::CONFIG_CACHE_KEY);
        if ($configs == null || $configs == false) {
            return null; // 代表未读取到任何值。
        } else {
            return $configs[$cfgKey] ?? null;
        }
    }
}