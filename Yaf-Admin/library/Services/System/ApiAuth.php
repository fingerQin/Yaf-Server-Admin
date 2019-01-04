<?php
/**
 * API 应用密钥管理。
 * @author fingerQin
 * @date 2018-07-10
 */

namespace Services\System;

use finger\Validator;
use finger\Database\Db;
use Utils\YCore;
use Models\ApiAuth as ApiAuthModel;

class ApiAuth extends \Services\AbstractBase
{
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
        $columns      = ['id', 'api_type', 'api_name', 'api_key', 'api_secret'];
        $ApiAuthModel = new ApiAuthModel();
        $appinfo      = $ApiAuthModel->fetchOne([], $where);
        if (empty($appinfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '应用不存在或已经删除');
        }
        return $appinfo;
    }

    /**
     * 编辑 APP 应用。
     * 
     * @param  int     $adminId    管理员ID。
     * @param  int     $id         应用ID。
     * @param  string  $apiType    应用类型。
     * @param  string  $apiName    应用名称。
     * @param  string  $apiKey     应用 KEY。
     * @param  string  $apiSecret  应用 密钥。
     * @return void
     */
    public static function edit($adminId, $id, $apiType, $apiName, $apiKey, $apiSecret)
    {
        // [1] 验证
        $data = [
            'api_type'   => $apiType,
            'api_name'   => $apiName,
            'api_key'    => $apiKey,
            'api_secret' => $apiSecret
        ];
        $rules = [
            'api_type'   => '应用类型|require|alpha|len:1:10:0',
            'api_name'   => '应用名称|require|len:1:20:1',
            'api_key'    => '应用标识|require|alpha_dash|len:1:20:0',
            'api_secret' => '应用密钥|require|alpha_dash|len:32:32:0'
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
        $data['u_time'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $ok             = $ApiAuthModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 添加 APP 应用。
     * 
     * @param  int     $adminId    管理员ID。
     * @param  string  $apiType    应用类型。
     * @param  string  $apiName    应用名称。
     * @param  string  $apiKey     应用 KEY。
     * @param  string  $apiSecret  应用 密钥。
     * @return void
     */
    public static function add($adminId, $apiType, $apiName, $apiKey, $apiSecret)
    {
        // [1] 验证
        $data = [
            'api_type'   => $apiType,
            'api_name'   => $apiName,
            'api_key'    => $apiKey,
            'api_secret' => $apiSecret
        ];
        $rules = [
            'api_type'   => '应用类型|require|alpha|len:1:10:0',
            'api_name'   => '应用名称|require|len:1:20:1',
            'api_key'    => '应用标识|require|alpha_dash|len:1:20:0',
            'api_secret' => '应用密钥|require|alpha_dash|len:32:32:0'
        ];
        Validator::valido($data, $rules); // 验证不通过会抛异常。
        if (!in_array($apiType, self::$apiTypeDict)) {
            YCore::exception(STATUS_SERVER_ERROR, '应用类型不合法');
        }
        $data['c_time'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $data['u_time'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $ApiAuthModel   = new ApiAuthModel();
        $id             = $ApiAuthModel->insert($data);
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
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }
}
