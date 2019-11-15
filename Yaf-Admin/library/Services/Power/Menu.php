<?php
/**
 * 菜单管理。
 * 
 * @author fingerQin
 * @date 2018-07-06
 */

namespace Services\Power;

use finger\Utils\YCore;
use finger\Validator;
use finger\Database\Db;
use Models\AdminMenu;
use Models\AdminRolePriv;

class Menu extends \Services\AbstractBase
{
    /**
     * 获取指定角色且指定父菜单的子菜单。
     * 
     * @param  int  $roleid    角色ID。
     * @param  int  $parentid  父菜单ID。
     * @return array
     */
    public static function getRoleSubMenu($roleid, $parentid)
    {
        if ($roleid == ROOT_ROLE_ID) { // 超级管理员验证角色权限。
            return self::getSubMenu($parentid);
        } else {
            $sql = 'SELECT b.* FROM finger_admin_role_priv AS a INNER JOIN finger_admin_menu AS b '
                 . 'ON(a.menuid=b.menuid AND a.roleid = :roleid AND b.parentid = :parentid) '
                 . 'WHERE b.is_display = :display ORDER BY b.listorder ASC,b.menuid ASC';
            $params = [
                ':parentid' => $parentid,
                ':roleid'   => $roleid,
                ':display'  => AdminMenu::STATUS_YES
            ];
            return Db::all($sql, $params);
        }
    }

    /**
     * 获取指定ID的子菜单。
     * 
     * @param  int  $parentId   父ID。
     * @param  int  $isGetHide  是否获取隐藏的菜单。
     * @return array
     */
    public static function getSubMenu($parentId, $isGetHide = false)
    {
        $where = [
            'parentid' => $parentId
        ];
        if ($isGetHide == false) {
            $where['is_display'] = AdminMenu::STATUS_YES;
        }
        $order = 'listorder ASC, menuid ASC';
        return (new AdminMenu())->fetchAll([], $where, 0, $order);
    }

    /**
     * 获取管理后台左侧菜单。
     * 
     * @param  int  $roleid  角色ID。
     * @param  int  $menuId  菜单ID。
     * @return array
     */
    public static function getLeftMenu($roleid, $menuId)
    {
        $menus = self::getRoleSubMenu($roleid, $menuId);
        if (empty($menus)) {
            return [];
        }
        foreach ($menus as $key => $menu) {
            $menus[$key]['sub_menu'] = self::getRoleSubMenu($roleid, $menu['menuid']);
        }
        return $menus;
    }

    /**
     * 获取菜单列表[tree]。
     * 
     * @param  int     $parentid      父ID。默认值0。
     * @param  string  $childrenName  子节点键名。
     * @return array
     */
    public static function getMenus($parentid = 0, $childrenName = 'sub')
    {
        $menus = self::getByParentToMenu($parentid);
        if (empty($menus)) {
            return $menus;
        } else {
            foreach ($menus as $key => $menu) {
                $menus[$key][$childrenName] = self::getMenus($menu['menuid']);
            }
            return $menus;
        }
    }

    /**
     * 通过父分类ID读取子菜单。
     * 
     * @param  int  $parentid   父分类ID。
     * @param  int  $isGetHide  是否获取隐藏的菜单。
     * 
     * @return array
     */
    public static function getByParentToMenu($parentid, $isGetHide = true)
    {
        $allMenus = self::getAllMenus();
        $menus    = [];
        foreach ($allMenus as $menu) {
            if (!$isGetHide && $menu['display'] == 0) {
                continue;
            }
            if ($menu['parentid'] == $parentid) {
                $arrKey         = "{$menu['listorder']}_{$menu['menuid']}";
                $menus[$arrKey] = $menu;
            }
        }
        ksort($menus);
        return $menus;
    }

    /**
     * 获取菜单详情。
     * 
     * @param  int  $menuId  菜单ID。
     * @return array
     */
    public static function getDetail($menuId)
    {
        return (new AdminMenu())->fetchOne([], ['menuid' => $menuId]);
    }

    /**
     * 获取菜单面包屑。
     * 
     * @param  int     $menuId  菜单ID。
     * @param  string  $crumbs  面包屑。
     * @return string
     */
    public static function getMenuCrumbs($menuId, $crumbs = '')
    {
        $menu = self::getDetail($menuId);
        if ($menu && $menu['parentid'] > 0) {
            $crumbs = " {$menu['menu_name']} > {$crumbs}";
            return self::getMenuCrumbs($menu['parentid'], $crumbs);
        } else {
            return "{$menu['menu_name']} > {$crumbs}";
        }
    }

    /**
     * 检查角色是否包含此菜单。
     *
     * @param  int     $roleid  角色 ID。
     * @param  string  $ctrl    控制器。
     * @param  string  $action  操作。
     *
     * @return bool
     */
    public static function checkRoleHasMenu($roleid, $ctrl, $action)
    {
        if ($roleid == ROOT_ROLE_ID) { // 超级管理员无敌权限。
            return true;
        }
        $roleMenus = self::getRoleAllMenu($roleid);
        foreach ($roleMenus as $menu) {
            if (strtolower($menu['c']) == strtolower($ctrl) && strtolower($menu['a']) == strtolower($action)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取指定角色所有的菜单。
     *
     * @param  int  $roleid  角色 ID。
     *
     * @return array
     */
    public static function getRoleAllMenu($roleid)
    {
        $cacheKey = "role_all_menus_{$roleid}";
        if (\Yaf_Registry::has($cacheKey)) {
            return \Yaf_Registry::get($cacheKey);
        } else {
            $sql = 'SELECT c,a FROM finger_admin_role_priv AS a '
                 . 'LEFT JOIN finger_admin_menu AS b ON(a.menuid=b.menuid) '
                 . 'WHERE a.roleid = :roleid AND b.c != "" AND b.a != ""';
            $params = [
                ':roleid' => $roleid
            ];
            $result = Db::query($sql, $params);
            \Yaf_Registry::set($cacheKey, $result);
            return $result;
        }
    }

    /**
     * 添加菜单。
     * 
     * @param  int     $parentid    父菜单ID。
     * @param  string  $name        菜单名称。
     * @param  string  $ctrlName    控制器名称。
     * @param  string  $actionName  操作名称。
     * @param  string  $icon        icon 图标样式。
     * @param  string  $data        附加参数。
     * @param  int     $listorder   排序。
     * @param  int     $display     是否显示。
     * @return void
     */
    public static function add($parentid, $name, $ctrlName, $actionName, $icon, $data, $listorder, $display = 0)
    {
        self::checkMenuName($name);
        self::checkMenuControllerName($parentid, $ctrlName);
        self::checkMenuActionName($parentid, $actionName);
        self::checkMenuAdditionData($data);
        $listorder = intval($listorder);
        $display   = intval($display);
        $parentid  = intval($parentid);
        $data = [
            'menu_name'  => $name,
            'parentid'   => $parentid,
            'c'          => $ctrlName,
            'a'          => $actionName,
            'icon'       => $icon,
            'ext_param'  => $data,
            'listorder'  => $listorder,
            'is_display' => $display,
            'c_by'       => 0
        ];
        $AdminMenuModel = new AdminMenu();
        $ok = $AdminMenuModel->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 编辑菜单。
     * 
     * @param  int     $menuId      菜单ID。
     * @param  int     $parentid    父菜单ID。
     * @param  string  $name        菜单名称。
     * @param  string  $ctrlName    控制器名称。
     * @param  string  $actionName  操作名称。
     * @param  string  $icon        icon 图标样式。
     * @param  string  $data        附加参数。
     * @param  int     $listorder   排序。
     * @param  int     $display     是否显示。
     * @return void
     */
    public static function edit($menuId, $parentid, $name, $ctrlName, $actionName, $icon, $data, $listorder, $display = 0)
    {
        self::checkMenuName($name);
        self::checkMenuControllerName($parentid, $ctrlName);
        self::checkMenuActionName($parentid, $actionName);
        self::checkMenuAdditionData($data);
        $listorder = intval($listorder);
        $display   = intval($display);
        $parentid  = intval($parentid);
        self::isExist($menuId);
        $data = [
            'menu_name'  => $name,
            'parentid'   => $parentid,
            'c'          => $ctrlName,
            'a'          => $actionName,
            'icon'       => $icon,
            'ext_param'  => $data,
            'listorder'  => $listorder,
            'is_display' => $display
        ];
        $AdminMenuModel = new AdminMenu();
        $where = ['menuid' => $menuId];
        $ok = $AdminMenuModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除菜单。
     * 
     * @param  int  $menuId  菜单ID。
     * @return void
     */
    public static function delete($menuId)
    {
        self::isExist($menuId);
        $AdminMenumodel = new AdminMenu();
        $subMenu        = $AdminMenumodel->fetchAll([], ['parentid' => $menuId]);
        if ($subMenu) {
            YCore::exception(STATUS_SERVER_ERROR, '请先移除该菜单下的子菜单再删除');
        }
        $where = ['menuid' => $menuId];
        $ok = $AdminMenumodel->delete($where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 菜单排序。
     * 
     * @param  array  $listorders  菜单排序数据。[ ['菜单ID' => '排序值'], ...... ]
     * @return void
     */
    public static function sort($listorders)
    {
        if (empty($listorders)) {
            return;
        }
        $AdminMenuModel = new AdminMenu();
        foreach ($listorders as $menuId => $sortVal) {
            $ok = $AdminMenuModel->sort($menuId, $sortVal);
            if (!$ok) {
                return YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
            }
        }
    }

    /**
     * 获取全部菜单。
     * 
     * @return array
     */
    protected static function getAllMenus()
    {
        $cacheKey = 'finger_get_all_menus';
        if (\Yaf_Registry::has($cacheKey)) {
            return \Yaf_Registry::get($cacheKey);
        } else {
            $where     = [];
            $columns   = [];
            $MenuModel = new AdminMenu();
            $result    = $MenuModel->fetchAll($columns, $where);
            \Yaf_Registry::set($cacheKey, $result);
            return $result;
        }
    }

    /**
     * 获取同菜单下面的所有操作
     * 
     * @param  string  $ctrl  控制器名
     * 
     * @return array
     */
    public static function menuActionList($ctrl)
    {
        $where = [
            'c' => $ctrl
        ];
        $columns = ['menuid', 'menu_name', 'c', 'a'];
        return (new AdminMenu())->fetchAll($columns, $where, 0, 'listorder ASC');
    }

    /**
     * 获取角色对应的权限菜单(树形结构)。
     * 
     * @param  int  $roleid  角色ID。
     * @return array
     */
    public static function getRolePermissionMenu($roleid)
    {
        Role::isExist($roleid);
        $AdminRolePrivModel = new AdminRolePriv();
        $list = $AdminRolePrivModel->fetchAll([], ['roleid' => $roleid]);
        $privMenus = []; // 只存在菜单ID。
        foreach ($list as $menu) {
            $privMenus[] = $menu['menuid'];
        }
        return $privMenus;
    }

    /**
     * 菜单是否存在。
     * 
     * @param  integer  $menuId  菜单ID。
     * @param  string   $errMsg  自定义错误信息。
     * @return array
     */
    public static function isExist($menuId, $errMsg = '')
    {
        $AdminMenuModel = new AdminMenu();
        $menuInfo       = $AdminMenuModel->fetchOne([], ['menuid' => $menuId]);
        if (empty($menuInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '菜单不存在或已经删除');
        }
        return $menuInfo;
    }

    /**
     * 检查指定角色的菜单权限。
     * 
     * @param  int     $roleid      角色ID。
     * @param  string  $ctrlName    控制器名称。
     * @param  string  $actionName  操作名称。
     * @return bool
     */
    public static function checkMenuPower($roleid, $ctrlName, $actionName)
    {
        if ($roleid == ROOT_ROLE_ID) {
            return true; // 超级管理员组拥有绝对的权限。
        }
        $AdminMenuModel = new AdminMenu();
        $where = [
            'c' => $ctrlName,
            'a' => $actionName
        ];
        $menuInfo = $AdminMenuModel->fetchOne([], $where);
        if (empty($menuInfo)) {
            return false;
        }
        $where = [
            'roleid' => $roleid,
            'menuid' => $menuInfo['menuid']
        ];
        $AdminRolePrivModel = new AdminRolePriv();
        $priv = $AdminRolePrivModel->fetchOne([], $where);
        if (empty($priv)) {
            return false;
        }
        return true;
    }

    /**
     * 检查资源菜单名称。
     * 
     * @param  string  $name  菜单名称。
     * @return void
     */
    public static function checkMenuName($name)
    {
        $data = [
            'menu_name' => $name
        ];
        $rules = [
            'menu_name' => '菜单名称|require|len:2:40:1',
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查资源菜单控制器名称。
     * 
     * @param  int     $parentid        父级菜单ID。
     * @param  string  $controllerName  菜单控制器名称。
     * @return void
     */
    public static function checkMenuControllerName($parentid, $controllerName)
    {
        if ($parentid == 0) {
            return;
        }
        $data = [
            'name' => $controllerName
        ];
        $rules = [
            'name' => '控制器名称|require',
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查资源菜单操作名称。
     * 
     * @param  int     $parentid    父级菜单ID。
     * @param  string  $actionName  菜单操作名称。
     * @return void
     */
    public static function checkMenuActionName($parentid, $actionName)
    {
        if ($parentid == 0) {
            return;
        }
        $data = [
            'name' => $actionName
        ];
        $rules = [
            'name' => '操作名称|require',
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查资源菜单附加参数。
     * 
     * @param  string  $additionData  菜单操作名称。
     * @return void
     */
    public static function checkMenuAdditionData($additionData)
    {
        $data = [
            'ext_param' => $additionData
        ];
        $rules = [
            'ext_param' => '附加参数|len:0:100:1',
        ];
        Validator::valido($data, $rules);
    }
}