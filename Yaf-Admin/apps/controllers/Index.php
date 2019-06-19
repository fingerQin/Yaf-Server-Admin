<?php
/**
 * 默认 controller。
 * @author fingerQin
 * @date 2018-06-27
 */

use Utils\YUrl;
use Services\Power\Menu;
use Services\System\Upload;
use Services\Power\Help;

class IndexController extends \Common\controllers\Admin
{
    /**
     * 管理后台首页。
     */
    public function indexAction()
    {
        $menuId   = $this->getInt('menu_id', 1);
        $topMenu  = Menu::getRoleSubMenu($this->roleid, 0);
        $leftMenu = Menu::getLeftMenu($this->roleid, $menuId);
        $this->assign('realname', $this->realname);
        $this->assign('mobilephone', $this->mobile);
        $this->assign('top_menu', $topMenu);
        $this->assign('left_menu', $leftMenu);
    }

    /**
     * 取左侧菜单。
     */
    public function leftAction()
    {
        $menuId   = $this->getInt('menu_id');
        $leftMenu = Menu::getLeftMenu($this->roleid, $menuId);
        $html     = $this->render('left', ['left_menu' => $leftMenu]);
        $this->json(true, 'success', ['html' => $html]);
    }

    /**
     * 面包屑。
     */
    public function arrowAction()
    {
        $menuId = $this->getInt('menu_id');
        echo Menu::getMenuCrumbs($menuId);
        $this->end();
    }

    /**
     * 默认内容页。
     */
    public function rightAction()
    {
        $gdInfo = gd_info();
        $systemInfo = [
            'os'           => PHP_OS,
            'php_version'  => PHP_VERSION,
            'zend_version' => zend_version(),
            'upload_size'  => get_cfg_var('upload_max_filesize') ? get_cfg_var('upload_max_filesize') : '不允许上传附件',
            'gd_info'      => $gdInfo['GD Version'],
        ];
        $this->assign('osinfo', $systemInfo);
    }

    /**
     * 文件上传。
     */
    public function uploadAction()
    {
        header("Access-Control-Allow-Origin: *");
        $result = Upload::uploadImage(1, $this->adminId, 'voucher', 2, 'uploadfile');
        $this->json(true, '上传成功', $result);
        $this->end();
    }

    /**
     * 设置帮助文档。
     */
    public function setHelpAction()
    {
        $ctrlName   = $this->getString('c');
        $actionName = $this->getString('a');
        $content    = $this->getString('content');
        Help::set($ctrlName, $actionName, $content);
        $this->json(true, '设置成功');
    }

    /**
     * 获取帮助文档。
     */
    public function getHelpAction()
    {
        $ctrlName        = $this->getString('c');
        $actionName      = $this->getString('a');
        $helpstr         = Help::get($ctrlName, $actionName);
        $filesDomainDame = YUrl::getFilesDomainName();
        $this->assign('c', $ctrlName);
        $this->assign('a', $actionName);
        $this->assign('files_domain_name', $filesDomainDame);
        $this->assign('helpstr', $helpstr);
    }
}