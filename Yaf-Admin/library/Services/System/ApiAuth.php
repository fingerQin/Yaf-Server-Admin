<?php
/**
 * API 应用密钥管理。
 * @author fingerQin
 * @date 2018-07-10
 */

namespace Services\System;

use Utils\YCore;
use Utils\YCache;
use finger\Validator;
use finger\Database\Db;
use Models\ApiAuth as ApiAuthModel;

class ApiAuth extends \Services\AbstractBase
{
    /**
     * Api 密钥缓存 KEY。
     */
    const API_AUTH_CACHE_KEY = 'api-auth-cache';

    /**
     * 应用类型。
     * 
     * -- 应用类型决定了能调用接口的品类。
     * 
     * @var array
     */
    private static $apiTypeDict = ['app', 'admin', 'activity'];

    /**
     * 获取应用详情。
     * 
     * @param  int  $id 应用记录 ID。
     * @return array
     */
    public static function detail($id)
    {
        $where = [
            'id'         => $id,
            'api_status' => ApiAuthModel::STATUS_YES
        ];
        $columns      = ['id', 'api_type', 'api_name', 'api_key', 'api_secret', 'is_open_ip_ban', 'ip_scope', 'ip_pool'];
        $ApiAuthModel = new ApiAuthModel();
        $appinfo      = $ApiAuthModel->fetchOne($columns, $where);
        if (empty($appinfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '应用不存在或已经删除');
        }
        $appinfo['ip_scope'] = str_replace('|', "\r\n", $appinfo['ip_scope']);
        $appinfo['ip_pool']  = str_replace('|', "\r\n", $appinfo['ip_pool']);
        return $appinfo;
    }

    /**
     * 编辑 APP 应用。
     * 
     * @param  int     $adminId      管理员ID。
     * @param  int     $id           应用ID。
     * @param  string  $apiType      应用类型。
     * @param  string  $apiName      应用名称。
     * @param  string  $apiKey       应用 KEY。
     * @param  string  $apiSecret    应用 密钥。
     * @param  int     $isOpenIpBan  是否限制 IP 访问。
     * @param  string  $ipScope      IP 段。
     * @param  string  $ipPool       ip 池。
     * @return void
     */
    public static function edit($adminId, $id, $apiType, $apiName, $apiKey, $apiSecret, $isOpenIpBan, $ipScope, $ipPool)
    {
        // [1] 验证
        $data = [
            'api_type'       => $apiType,
            'api_name'       => $apiName,
            'api_key'        => $apiKey,
            'api_secret'     => $apiSecret,
            'is_open_ip_ban' => $isOpenIpBan,
            'ip_scope'       => $ipScope,
            'ip_pool'        => $ipPool
        ];
        $rules = [
            'api_type'       => '应用类型|require|alpha|len:1:10:0',
            'api_name'       => '应用名称|require|len:1:20:1',
            'api_key'        => '应用标识|require|alpha_dash|len:1:20:0',
            'api_secret'     => '应用密钥|require|alpha_dash|len:32:32:0',
            'is_open_ip_ban' => '是否限制IP访问|require|integer|number_between:0:1',
            'ip_scope'       => 'IP段|len:0:400:0',
            'ip_pool'        => 'IP池|len:0:5000:0'
        ];
        Validator::valido($data, $rules); // 验证不通过会抛异常。
        if (!in_array($apiType, self::$apiTypeDict)) {
            YCore::exception(STATUS_SERVER_ERROR, 'APP类型不合法');
        }
        // [2] 记录存在与否。
        $where = [
            'id'         => $id,
            'api_status' => ApiAuthModel::STATUS_YES
        ];
        $ApiAuthModel = new ApiAuthModel();
        $appinfo      = $ApiAuthModel->fetchOne([], $where);
        if (empty($appinfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '记录不存在或已经删除');
        }
        // [3] 更新。
        $data['u_time']   = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $data['ip_scope'] = self::ipScoreFormatterToSave($ipScope);
        $data['ip_pool']  = self::ipPoolFormatterToSave($ipPool);
        $ok = $ApiAuthModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 添加 APP 应用。
     * 
     * @param  int     $adminId      管理员ID。
     * @param  string  $apiType      应用类型。
     * @param  string  $apiName      应用名称。
     * @param  string  $apiKey       应用 KEY。
     * @param  string  $apiSecret    应用 密钥。
     * @param  int     $isOpenIpBan  是否限制 IP 访问。
     * @param  string  $ipScope      IP 段。
     * @param  string  $ipPool       ip 池。
     * @return void
     */
    public static function add($adminId, $apiType, $apiName, $apiKey, $apiSecret, $isOpenIpBan, $ipScope, $ipPool)
    {
        // [1] 验证
        $data = [
            'api_type'       => $apiType,
            'api_name'       => $apiName,
            'api_key'        => $apiKey,
            'api_secret'     => $apiSecret,
            'is_open_ip_ban' => $isOpenIpBan,
            'ip_scope'       => $ipScope,
            'ip_pool'        => $ipPool
        ];
        $rules = [
            'api_type'       => '应用类型|require|alpha|len:1:10:0',
            'api_name'       => '应用名称|require|len:1:20:1',
            'api_key'        => '应用标识|require|alpha_dash|len:1:20:0',
            'api_secret'     => '应用密钥|require|alpha_dash|len:32:32:0',
            'is_open_ip_ban' => '是否限制IP访问|require|integer|number_between:0:1',
            'ip_scope'       => 'IP段|len:0:400:0',
            'ip_pool'        => 'IP池|len:0:5000:0'
        ];
        Validator::valido($data, $rules); // 验证不通过会抛异常。
        if (!in_array($apiType, self::$apiTypeDict)) {
            YCore::exception(STATUS_SERVER_ERROR, '应用类型不合法');
        }
        $data['c_time']   = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $data['u_time']   = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $data['ip_scope'] = self::ipScoreFormatterToSave($ipScope);
        $data['ip_pool']  = self::ipPoolFormatterToSave($ipPool);
        $ApiAuthModel     = new ApiAuthModel();
        $id = $ApiAuthModel->insert($data);
        if ($id == 0) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除应用。
     * 
     * @param  int  $adminId  管理员ID。
     * @param  int  $id       应用ID。
     * @return void
     */
    public static function delete($adminId, $id)
    {
        $where = [
            'id'         => $id,
            'api_status' => ApiAuthModel::STATUS_YES
        ];
        $appModel = new ApiAuthModel();
        $appinfo  = $appModel->fetchOne([], $where);
        if (empty($appinfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '应用不存在或已经删除');
        }
        $updata = [
            'api_status' => ApiAuthModel::STATUS_DELETED,
            'u_time'     => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
        ];
        $ok = $appModel->update($updata, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '删除失败');
        }
        self::clearCache();
    }

    /**
     * 获取 APP 应用列表。
     * 
     * @param int   $page    当前页码。
     * @param int   $count   每页显示条数。
     * @return array
     */
    public static function list($page = 1, $count = 20)
    {
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' id, api_type, api_name, api_key, api_secret, c_time, u_time';
        $where   = ' WHERE api_status = :api_status ';
        $params  = [
            ':api_status' => ApiAuthModel::STATUS_YES
        ];
        $orderBy   = ' ORDER BY id ASC ';
        $sql       = "SELECT COUNT(1) AS count FROM finger_api_auth {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} FROM finger_api_auth {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        $result    = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * IP 段格式为为数据库保存结果。
     * 
     * -- 会过滤非法的 IP 地址。
     *
     * @param  string  $ipScope  IP 段。
     *
     * @return string
     */
    protected static function ipScoreFormatterToSave($ipScope = '')
    {
        if (strlen($ipScope) == 0) {
            return '';
        }
        $ipFilterResult = [];
        $ipScopeArr     = explode("\r\n", $ipScope);
        foreach ($ipScopeArr as $ipScopeSingle) {
            $ipScopeSingle = str_replace(' ', '', $ipScopeSingle);
            $ipScopeSingle = explode('-', $ipScopeSingle);
            if (count($ipScopeSingle) != 2) {
                continue;
            }
            if (Validator::is_ip($ipScopeSingle[0]) == false) {
                continue;
            }
            if (Validator::is_ip($ipScopeSingle[1]) == false) {
                continue;
            }
            $startIpInt = ip2long($ipScopeSingle[0]);
            $endIpInt   = ip2long($ipScopeSingle[1]);
            if ($startIpInt >= $endIpInt) { // 起始 IP 必须小于截止 IP。
                continue;
            }
            $ipFilterResult[] = "{$ipScopeSingle[0]}-{$ipScopeSingle[1]}";
        }
        return implode('|', $ipFilterResult);
    }

    /**
     * IP 池格式化为数据库保存结果。
     *
     * @param  string  $ipPool  IP 池。
     *
     * @return string
     */
    protected static function ipPoolFormatterToSave($ipPool)
    {
        if (strlen($ipPool) == 0) {
            return '';
        }
        $ipFilterResult = [];
        $ipPoolArr      = explode("\r\n", $ipPool);
        $ipPool         = str_replace(' ', '', $ipPool);
        foreach ($ipPoolArr as $ip) {
            if (!Validator::is_ip($ip)) {
                continue;
            }
            $ipFilterResult[] = $ip;
        }
        $ipFilterResult = array_unique($ipFilterResult);
        return implode('|', $ipFilterResult);
    }

    /**
     * 清除缓存。
     *
     * @return void
     */
    public static function clearCache()
    {
        $redis = YCache::getRedisClient();
        $redis->del(self::API_AUTH_CACHE_KEY);
    }
}
