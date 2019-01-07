<?php
/**
 * 角色管理。
 * @author fingerQin
 * @date 2018-07-07
 */

use Utils\YCore;
use Services\Power\Role;
use Services\Power\Menu;

class RoleController extends \Common\controllers\Admin
{
    /**
     * 角色列表。
     */
    public function indexAction()
    {
        $roles = Role::list();
        $this->assign('roles', $roles);
    }

    /**
     * Ajax 方式获取角色列表。
     */
    public function ajaxRoleListAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $roles = Role::list();
            $this->json(true, 'success', $roles);
        }
        $this->end();
    }

    /**
     * 添加角色。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $rolename    = $this->getString('rolename');
            $listorder   = $this->getInt('listorder');
            $description = $this->getString('description');
            Role::add($rolename, $listorder, $description);
            $this->json(true, '添加成功');
        }
    }

    /**
     * 编辑角色。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $roleid      = $this->getInt('roleid');
            $rolename    = $this->getString('rolename');
            $listorder   = $this->getInt('listorder');
            $description = $this->getString('description');
            Role::edit($roleid, $rolename, $listorder, $description);
            $this->json(true, '修改成功');
        }
        $roleid = $this->getInt('roleid');
        $role   = Role::detail($roleid);
        $this->assign('role', $role);
    }

    /**
     * 删除角色。
     */
    public function deleteAction()
    {
        $roleid = $this->getInt('roleid');
        Role::delete($roleid);
        $this->json(true, '删除成功');
        $this->end();
    }
  
    /**
     * 设置角色权限。
     */
    public function setPermissionAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $roleid     = $this->getInt('roleid');
            $arrMenuIds = $this->getArray('menuid', []);
            Role::setPermission($this->adminId, $roleid, $arrMenuIds);
            $this->json(true, '设置成功');
        }
    }

    /**
     * 获取角色权限的菜单ID。
     */
    public function getRolePermissionMenuAction() {
        $roleid    = $this->getInt('roleid');
        $privMenus = Menu::getRolePermissionMenu($roleid);
        $menus     = Menu::getMenus(0);
        $this->assign('menus', $menus);
        $this->assign('roleid', $roleid);
        $this->assign('priv_menus', $privMenus);
    }
}