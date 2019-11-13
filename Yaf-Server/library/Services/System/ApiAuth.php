<?php
/**
 * App 密钥管理。
 * @author fingerQin
 * @date 2019-05-22
 */

namespace Services\System;

use finger\Ip;
use finger\Utils\YCache;
use finger\Utils\YCore;
use Models\ApiAuth as ApiAuthModel;

class ApiAuth extends \Services\AbstractBase
{
    /**
     * Api 密钥缓存 KEY。
     */
    const API_AUTH_CACHE_KEY = 'api-auth-cache';

    /**
     * 检查 IP 是否允许访问。
     * 
     * -- 只要检测到当前指定 IP 被 IP 段或 IP 池禁止访问就立即停止检测。
     *
     * @param  array   $detail  Api 配置信息。
     * @param  string  $ip      IP 地址。
     * 
     * @return bool
     */
    public static function checkIpAllowAccess(&$detail, $ip)
    {
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '应用配置信息读取异常');
        }
        if ($detail['is_open_ip_ban'] != 1) {
            return true;
        }

        $envName = YCore::appconfig('app.env');
        $envName = \strtolower($envName);
        if (!in_array($envName, [ENV_BETA, ENV_PRO])) { // 如果当前环境不是公测/正式。则不做 IP 限制。
            return true;
        }
        $appType = strtolower($detail['api_type']);
        if ($appType == ApiAuthModel::API_TYPE_APP) { // APP 调用的接口不受 IP 白名单限制。
            return true;
        }

        $isAllowAccess = true;
        if ($isAllowAccess && !self::checkIpScope($detail['ip_scope'], $ip)) {
            $isAllowAccess = false;
        }
        if ($isAllowAccess && !self::checkIpPool($detail['ip_pool'], $ip)) {
            $isAllowAccess = false;
        }
        return $isAllowAccess;
    }

    /**
     * 验证 IP 是否在 IP 池中。
     * 
     * -- 如果未设置说明不限制。
     *
     * @param  string  $ipPool  IP 池。
     * @param  string  $ip      IP。
     *
     * @return bool
     */
    protected static function checkIpPool($ipPool, $ip)
    {
        if (strlen($ipPool) == 0) {
            return true;
        }
        $ipPool = explode('|', $ipPool);
        return in_array($ip, $ipPool) ? true : false;
    }

    /**
     * 验证 IP 是否在 IP 段中。
     * 
     * -- 如果未设置说明不限制。
     *
     * @param  string  $ipScope  IP 段。
     * @param  string  $ip       IP。
     *
     * @return bool
     */
    protected static function checkIpScope($ipScope, $ip)
    {
        if (strlen($ipScope) == 0) {
            return true;
        }
        $ipScopeArr = explode('|', $ipScope);
        foreach ($ipScopeArr as $ips) {
            $ipsArr = explode('-', $ips);
            if (count($ipsArr) != 2) {
                YCore::exception(STATUS_SERVER_ERROR, 'IP 段配置有误');
            }
            if (Ip::isRange($ipsArr[0], $ipsArr[1], $ip)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取接口配置详情。
     *
     * @param  int  $appid  应用 ID。
     * @return array
     */
    public static function getApiDetail($appid)
    {
        $allApiConfig = self::all();
        return isset($allApiConfig[$appid]) ? $allApiConfig[$appid] : [];
    }

    /**
     * 读取所有 API 权限配置。
     *
     * @return array
     */
    protected static function all()
    {
        $redis = YCache::getRedisClient();
        $cache = $redis->get(self::API_AUTH_CACHE_KEY);
        if ($cache === null || $cache === false) {
            $ApiAuthModel = new ApiAuthModel();
            $columns = ['api_type', 'api_key', 'api_secret', 'is_open_ip_ban', 'ip_scope', 'ip_pool'];
            $where   = ['api_status' => ApiAuthModel::STATUS_YES];
            $result  = $ApiAuthModel->fetchAll($columns, $where);
            if (empty($result)) {
                YCore::exception(STATUS_SERVER_ERROR, '系统配置异常,请联系客服');
            }
            $data = [];
            foreach ($result as $item) {
                $data[$item['api_key']] = $item;
            }
            $redis->set(self::API_AUTH_CACHE_KEY, json_encode($data, JSON_UNESCAPED_UNICODE));
            return $data;
        } else {
            return json_decode($cache, true);
        }
    }
}