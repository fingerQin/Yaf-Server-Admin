<?php
/**
 * 菜单管理。
 * @author fingerQin
 * @date 2018-07-07
 */

use Utils\YCore;
use Services\Power\Menu;

class MenuController extends \Common\controllers\Admin
{
    /**
     * 菜单列表。
     */
    public function indexAction()
    {
        $list = Menu::getMenus(0);
        $this->assign('list', $list);
    }

    /**
     * 添加菜单。
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $parentid   = $this->getInt('parentid', 0);
            $name       = $this->getString('name');
            $ctrlName   = $this->getString('c');
            $actionName = $this->getString('a');
            $ico        = $this->getString('ico','');
            $data       = $this->getString('data', '');
            $listorder  = $this->getInt('listorder', 0);
            $display    = $this->getInt('display', 0);
            Menu::add($parentid, $name, $ctrlName, $actionName,$ico, $data, $listorder, $display);
            $this->json(true, '添加成功');
        }
        $parentid = $this->getInt('parentid', 0);
        $menus    = Menu::getMenus(0);
        $this->assign('menus', $menus);
        $this->assign('parentid', $parentid);
    }

    /**
     * 编辑菜单。
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $menuId     = $this->getInt('menu_id');
            $parentid   = $this->getString('parentid');
            $name       = $this->getString('name');
            $ctrlName   = $this->getString('c');
            $actionName = $this->getString('a');
            $ico        = $this->getString('ico','');
            $data       = $this->getString('data');
            $listorder  = $this->getInt('listorder', 0);
            $display    = $this->getInt('display', 0);
            Menu::edit($menuId, $parentid, $name, $ctrlName, $actionName,$ico, $data, $listorder, $display);
            $this->json(true, '编辑成功');
        }
        $menuId = $this->getInt('menu_id');
        $detail = Menu::getDetail($menuId);
        $menus  = Menu::getMenus(0);
        $this->assign('detail', $detail);
        $this->assign('menus', $menus);
    }

    /**
     * 删除菜单。
     */
    public function deleteAction()
    {
        $menuId = $this->getInt('menu_id');
        Menu::delete($menuId);
        $this->json(true, '删除成功');
    }

    /**
     * 菜单排序。
     */
    public function sortAction()
    {
        if ($this->_request->isPost()) {
            $listorders = $this->getArray('listorders', []);
            Menu::sort($listorders);
            $this->json(true, '排序成功');
        }
    }

    /**
     * 选择菜单下面的方法
     */
    public function actionListAction()
    {
        $cName = $this->getString('ctrl_name', '');
        if($this->_request->isXmlHttpRequest()){
            $list = Menu::menuActionList($cName);
            if (!empty($list)) {
                $str = '<option value="">请选择</option>';
                foreach($list as $k => $v){
                    $str .= '<option value="'. $v['a'] .'">' .$v['menu_name']. '</option>';
                }
                $this->json(true, '', ['actionlist' => $str]);
            } else {
                $this->json(false, '没有数据', []);
            }
        }

    }
}