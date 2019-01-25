<?php
/**
 * 角色管理。
 * 
 * @author fingerQin
 * @date 2018-07-06
 */

namespace Services\Power;

use Utils\YCore;
use finger\Validator;
use finger\Database\Db;
use Models\AdminRole;
use Models\AdminUser;
use Models\AdminMenu;
use Models\AdminRolePriv;

class Role extends \Services\AbstractBase
{
    /**
     * 获取角色列表。
     *
     * @param  string  $isConvert  是否转换为一维的键值数组。['1' => '管理员', '2' => '普通管理员']
     * 
     * @return array
     */
    public static function list($isConvert = false)
    {
        $column = ['roleid', 'role_name', 'listorder', 'description', 'c_time'];
        $where  = ['role_status' => AdminRole::STATUS_YES];
        $roles  = (new AdminRole())->fetchAll($column, $where);
        $data   = [];
        if ($isConvert) {
            foreach ($roles as $role) {
                $data[$role['roleid']] = $role['role_name'];
            }
        } else {
            $data = $roles;
        }
        return $data;
    }

    /**
     * 添加角色。
     * 
     * @param  string  $rolename     角色名称。
     * @param  int     $listorder    排序。小在前。
     * @param  string  $description  角色介绍。
     * @return void
     */
    public static function add($rolename, $listorder = 0, $description = '')
    {
        // [1] 验证
        self::checkName($rolename);
        self::checkOrder($listorder);
        self::checkDescription($description);
        $data = [
            'role_name'    => $rolename,
            'listorder'    => $listorder,
            'description'  => $description,
            'is_default'   => AdminRole::STATUS_NO,
            'role_status'  => AdminRole::STATUS_YES,
            'c_by'         => 0
        ];
        $AdminRoleModel = new AdminRole();
        $roleid = $AdminRoleModel->insert($data);
        if ($roleid == 0) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 编辑角色。
     * 
     * @param  int     $roleid       角色ID。
     * @param  string  $rolename     角色名称。
     * @param  int     $listorder    排序。
     * @param  string  $description  角色介绍。
     * @return void
     */
    public static function edit($roleid, $rolename, $listorder = 0, $description = '')
    {
        // [1] 验证
        self::checkName($rolename);
        self::checkOrder($listorder);
        self::checkDescription($description);
        self::isExist($roleid);
        $where = ['roleid' => $roleid];
        $data  = [
            'role_name'   => $rolename,
            'listorder'   => $listorder,
            'description' => $description,
            'role_status' => AdminRole::STATUS_YES
        ];
        $AdminRoleModel = new AdminRole();
        $ok = $AdminRoleModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 角色删除。
     * 
     * @param  int  $roleid  角色ID。
     * @return void
     */
    public static function delete($roleid)
    {
        $roleInfo = self::isExist($roleid);
        if ($roleInfo['is_default'] == AdminUser::STATUS_YES || $roleid == ROOT_ROLE_ID) {
            YCore::exception(STATUS_SERVER_ERROR, '此角色不能删除!');
        }
        $AdminUserModel = new AdminUser();
        $adminCount     = $AdminUserModel->count([
            'roleid'      => $roleid,
            'user_status' => AdminUser::STATUS_YES
        ]);
        if ($adminCount == 0) {
            $AdminRoleModel = new AdminRole();
            $where = [
                'roleid'      => $roleid,
                'role_status' => AdminRole::STATUS_YES,
            ];
            $updata = [
                'role_status' => AdminRole::STATUS_DELETED,
                'u_time'      => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
            ];
            return $AdminRoleModel->update($updata, $where);
        } else {
            YCore::exception(STATUS_SERVER_ERROR, '请将该角色下的管理员移动到其它角色下');
        }
    }

    /**
     * 角色详情。
     * 
     * @param  int  $roleid  角色ID。
     * @return bool
     */
    public static function detail($roleid)
    {
        return self::isExist($roleid);
    }

    /**
     * 检查角色格式。
     * 
     * @param  int     $roleid  角色ID。
     * @param  string  $errMsg  自定义错误提示。
     * @return array
     */
    public static function isExist($roleid, $errMsg = '')
    {
        $AdminRoleModel = new AdminRole();
        $roleInfo       = $AdminRoleModel->fetchOne([], ['roleid' => $roleid, 'role_status' => AdminRole::STATUS_YES]);
        if (empty($roleInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '角色不存在或已经删除');
        }
        return $roleInfo;
    }

    /**
     * 设置角色权限。
     * 
     * @param  int    $adminId     管理员 ID。
     * @param  int    $roleid      角色 ID。
     * @param  array  $arrMenuIds  菜单 ID 数组。
     * @return void
     */
    public static function setPermission($adminId, $roleid, $arrMenuIds)
    {
        if (self::isRootRole($roleid)) {
            YCore::exception(STATUS_SERVER_ERROR, '超级管理员角色不需要设置');
        }
        Db::beginTransaction();
        // [1] 角色判断。
        self::isExist($roleid);
        // [2] 清空角色之前的数据。
        $AdminRolePrivModel = new AdminRolePriv();
        $AdminRolePrivModel->clearRolePriv($roleid);
        // [3] 添加权限到角色。
        foreach ($arrMenuIds as $menuId) {
            Menu::isExist($menuId);
            $ok = $AdminRolePrivModel->addRolePriv($adminId, $roleid, $menuId);
            if (!$ok) {
                Db::rollBack();
                YCore::exception(STATUS_SERVER_ERROR, '权限添加失败，请重试');
            }
        }
        Db::commit();
    }

    /**
     * 是否超级管理员角色。
     *
     * @param  int  $roleid  角色 ID。
     * @return bool
     */
    public static function isRootRole($roleid)
    {
        return ($roleid == ROOT_ROLE_ID) ? true : false;
    }

    /**
     * 检查角色名称格式。
     * 
     * @param  string  $rolename  角色名称。
     * @return void
     */
    public static function checkName($rolename)
    {
        $data = [
            'role_name' => $rolename
        ];
        $rules = [
            'role_name' => '角色|require|len:2:10:1'
        ];
        Validator::valido($data, $rules); // 验证不通过会抛异常。
    }

    /**
     * 检查角色介绍是否正确。
     * 
     * @param  string  $description  角色介绍。
     * @return void
     */
    public static function checkDescription($description)
    {
        $data = [
            'description' => $description
        ];
        $rules = [
            'description' => '角色介绍|require|len:1:100:1',
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查角色排序值格式。
     * 
     * @param  int  $listorder  排序值。
     * @return void
     */
    public static function checkOrder($listorder)
    {
        $data = [
            'listorder' => $listorder
        ];
        $rules = [
            'listorder' => '排序|require|integer',
        ];
        Validator::valido($data, $rules);
    }
}