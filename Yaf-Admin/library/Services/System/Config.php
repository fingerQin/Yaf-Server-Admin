<?php
/**
 * 系统配置管理。
 * @author fingerQin
 * @date 2016-01-29
 */

namespace Services\System;

use finger\Cache;
use finger\Core;
use finger\Validator;
use finger\Database\Db;
use Models\Config as ConfigModel;

class Config extends \Services\AbstractBase
{
    /**
     * 配置文件缓存 KEY。
     */
    const CONFIG_CACHE_KEY = 'system-configs';

    /**
     * 以键值对形式返回所有的配置数据。
     * 
     * @param  bool  $isReadCache  是否从缓存读取配置数据。用于管理后台读取实时配置。
     *
     * @return array
     */
    public static function getAllConfig($isReadCache = true)
    {
        $configs  = \Yaf_Registry::get(self::CONFIG_CACHE_KEY);
        if ($isReadCache === true && $configs !== null && $configs !== false) { // 保证每个请求只会调用一次Redis读取缓存的操作，节省Redis资源。
            return $configs;
        }
        $configsCache = YCache::get(self::CONFIG_CACHE_KEY);
        if ($isReadCache === false || $configsCache === false) {
            $ConfigModel = new ConfigModel();
            $columns = ['cfg_key', 'cfg_value'];
            $where   = [
                'cfg_status' => ConfigModel::STATUS_YES
            ];
            $orderBy = ' configid ASC ';
            $result  = $ConfigModel->fetchAll($columns, $where, 0, $orderBy);
            $configs = [];
            foreach ($result as $val) {
                $configs[$val['cfg_key']] = $val['cfg_value'];
            }
            Cache::set(self::CONFIG_CACHE_KEY, json_encode($configs, JSON_UNESCAPED_UNICODE));
            \Yaf_Registry::set(self::CONFIG_CACHE_KEY, $configs);
            return $configs;
        } else {
            $configs = json_decode($configsCache, true);
            \Yaf_Registry::set(self::CONFIG_CACHE_KEY, $configs);
            return $configs;
        }
    }

    /**
     * 以键值对形式返回所有的配置数据(直读数据库版)。
     * 
     * @return array
     */
    public static function directReadDbConfig()
    {
        // 先从当前请求中拿已经放入此中的配置数据。
        $configs = \Yaf_Registry::get(self::CONFIG_CACHE_KEY);
        if ($configs !== null && $configs !== false) { // 保证每个请求只会调用一次Redis读取缓存的操作，节省Redis资源。
            return $configs;
        }
        $ConfigModel = new ConfigModel();
        $columns = ['cfg_key', 'cfg_value'];
        $where   = [
            'cfg_status' => ConfigModel::STATUS_YES
        ];
        $orderBy = ' configid ASC ';
        $result  = $ConfigModel->fetchAll($columns, $where, 0, $orderBy);
        $configs = [];
        foreach ($result as $val) {
            $configs[$val['cfg_key']] = $val['cfg_value'];
        }
        \Yaf_Registry::set(self::CONFIG_CACHE_KEY, $configs);
        return $configs;
    }

    /**
     * 清除配置文件缓存。
     *
     * @return void
     */
    public static function clearCache()
    {
        Cache::delete(self::CONFIG_CACHE_KEY);
        \Yaf_Registry::del(self::CONFIG_CACHE_KEY);
    }

    /**
     * 获取配置列表。
     *
     * @param  string  $keyword  查询关键词。
     * @param  int     $page     当前页码。
     * @param  int     $count    每页显示条数。
     * @return array
     */
    public static function list($keyword = '', $page, $count)
    {
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' configid,title,cfg_key,cfg_value,description,c_time,u_time ';
        $where   = ' WHERE cfg_status = :status ';
        $params  = [
            ':status' => ConfigModel::STATUS_YES
        ];
        if (strlen($keyword) > 0) {
            $where .= ' AND ( cfg_key LIKE :cfg_key OR cfg_value LIKE :cfg_value )';
            $params[':cfg_key']   = "%{$keyword}%";
            $params[':cfg_value'] = "%{$keyword}%";
        }
        $orderBy   = ' ORDER BY configid ASC ';
        $sql       = "SELECT COUNT(1) AS count FROM finger_config {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} FROM finger_config {$where} {$orderBy} LIMIT {$offset},{$count}";
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
     * 添加配置。
     *
     * @param  int     $adminId      管理员ID。
     * @param  string  $title        配置标题。
     * @param  string  $cfgName      配置名称。
     * @param  string  $cfgValue     配置值。
     * @param  string  $description  配置描述。
     * @return void
     */
    public static function add($adminId, $title, $cfgName, $cfgValue, $description)
    {
        // [1] 验证
        $data = [
            'title'       => $title,
            'cfg_key'     => $cfgName,
            'cfg_value'   => $cfgValue,
            'description' => $description
        ];
        $rules = [
            'title'       => '配置标题|require|len:1:50:1',
            'cfg_key'     => '配置名称|require|alpha_dash|len:1:30:0',
            'cfg_value'   => '配置值|len:1:1000:1',
            'description' => '配置描述|len:0:255:1'
        ];
        Validator::valido($data, $rules); // 验证不通过会抛异常。
        $data['c_by']       = $adminId;
        $data['cfg_status'] = ConfigModel::STATUS_YES;
        $ConfigModel        = new ConfigModel();
        $configId           = $ConfigModel->insert($data);
        if ($configId == 0) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
        self::clearCache();
    }

    /**
     * 修改配置。
     *
     * @param  int     $adminId      管理员ID。
     * @param  int     $configId     配置ID。
     * @param  string  $title        配置标题。
     * @param  string  $cfgName      配置名称。
     * @param  string  $cfgValue     配置值。
     * @param  string  $description  配置描述。
     * @return void
     */
    public static function edit($adminId, $configId, $title, $cfgName, $cfgValue, $description)
    {
        // [1] 验证
        $data = [
            'title'       => $title,
            'cfg_key'     => $cfgName,
            'cfg_value'   => $cfgValue,
            'description' => $description
        ];
        $rules = [
            'title'       => '配置标题|require|len:1:50:1',
            'cfg_key'     => '配置名称|require|alpha_dash|len:1:30:0',
            'cfg_value'   => '配置值|len:1:1000:1',
            'description' => '配置描述|len:0:255:1'
        ];
        Validator::valido($data, $rules); // 验证不通过会抛异常。
        $ConfigModel = new ConfigModel();
        $where = [
            'configid'   => $configId,
            'cfg_status' => ConfigModel::STATUS_YES
        ];
        $configDetail = $ConfigModel->fetchOne([], $where);
        if (empty($configDetail)) {
            Core::exception(STATUS_SERVER_ERROR, '该配置不存在');
        }
        self::clearCache();
        $data['u_by'] = $adminId;
        $ok = $ConfigModel->update($data, $where);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 按配置编码更新配置值。
     *
     * @param  string  $cfgKey    配置编码。
     * @param  string  $cfgValue  配置值。
     * @return void
     */
    public static function updateConfigValue($cfgKey, $cfgValue)
    {
        $ConfigModel = new ConfigModel();
        if (!Validator::is_len($cfgValue, 1, 255, true)) {
            Core::exception(STATUS_SERVER_ERROR, '配置值必须小于255个字符');
        }
        $update = [
            'cfg_value' => $cfgValue,
            'u_time'    => date('Y-m-d H:i:s', time())
        ];
        $where = [
            'cfg_key'    => $cfgKey,
            'cfg_status' => ConfigModel::STATUS_YES
        ];
        $ok = $ConfigModel->update($update, $where);
        if (!$ok) {
            Core::exception(STATUS_SERVER_ERROR, '配置更新失败');
        }
        self::clearCache();
    }

    /**
     * 删除配置。
     *
     * @param  int  $adminId   管理员ID。
     * @param  int  $configId  配置ID。
     * @return void
     */
    public static function delete($adminId, $configId)
    {
        $ConfigModel = new ConfigModel();
        $where = [
            'configid'   => $configId,
            'cfg_status' => ConfigModel::STATUS_YES
        ];
        $configDetail = $ConfigModel->fetchOne([], $where);
        if (empty($configDetail) || $configDetail['cfg_status'] != ConfigModel::STATUS_YES) {
            Core::exception(STATUS_SERVER_ERROR, '配置不存在或已经删除');
        }
        $data = [
            'cfg_status' => ConfigModel::STATUS_DELETED,
            'u_by'       => $adminId,
            'u_time'     => date('Y-m-d H:i:s', time())
        ];
        self::clearCache();
        $ok = $ConfigModel->update($data, $where);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 获取配置详情。
     *
     * @param  int  $configId  配置ID。
     * @return array
     */
    public static function detail($configId)
    {
        $ConfigModel = new ConfigModel();
        $detail = $ConfigModel->fetchOne([], ['configid' => $configId]);
        if (empty($detail) || $detail['cfg_status'] != ConfigModel::STATUS_YES) {
            Core::exception(STATUS_SERVER_ERROR, '配置不存在或已经删除');
        }
        return $detail;
    }
}