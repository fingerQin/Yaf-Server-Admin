<?php
/**
 * 管理后台菜单表 Model。
 * @author fingerQin
 * @date 2018-07-06
 */

namespace Models;

class AdminRolePriv extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_admin_role_priv';

    protected $primaryKey = 'id';

    /**
     * 清空角色所有权限数据。
     *
     * @param  int  $roleid  角色ID。
     * @return bool
     */
    public function clearRolePriv($roleid)
    {
        $where = [
            'roleid' => $roleid
        ];
        return $this->delete($where);
    }

    /**
     * 获取角色全部的权限。
     *
     * @param  int  $roleid  角色ID。
     * @return void
     */
    public function getRolePriv($roleid)
    {
        $where = [
            'roleid' => $roleid
        ];
        return $this->fetchAll([], $where);
    }

    /**
     * 添加角色权限。
     *
     * @param  int     $adminId  管理员 ID。
     * @param  int     $roleid   角色 ID。
     * @param  string  $menuId   菜单 ID。
     * @return bool
     */
    public function addRolePriv($adminId, $roleid, $menuId)
    {
        $data = [
            'roleid' => $roleid,
            'menuid' => $menuId,
            'c_by'   => $adminId
        ];
        $id = $this->insert($data);
        return $id ? true : false;
    }
}