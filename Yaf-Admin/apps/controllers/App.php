<?php
/**
 * APP 版本管理。
 * @author fingerQin
 * @date 2018-07-10
 */

use finger\Paginator;
use Services\System\App;

class AppController extends \Common\controllers\Admin
{
    /**
     * APP 版本列表。
     */
    public function listAction()
    {
        $appType   = $this->getInt('appType', -1);
        $channel   = $this->getString('channel', '');
        $appV      = $this->getString('appV', '');
        $page      = $this->getInt('page', 1);
        $list      = App::list($appType, $channel, $appV, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('page_html', $pageHtml);
        $this->assign('list', $list['list']);
        $this->assign('appType', $appType);
        $this->assign('channel', $channel);
        $this->assign('appV', $appV);
        $this->assign('channelDict', App::$AndroidChannelDict);
    }

    /**
     * 添加应用。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $appType      = $this->getInt('app_type');
            $appTitle     = $this->getString('app_title');
            $appV         = $this->getString('app_v');
            $appDesc      = $this->getString('app_desc');
            $url          = $this->getString('url');
            $upgradeWay   = $this->getString('upgrade_way');
            $dialogRepeat = $this->getInt('dialog_repeat');
            $channel      = $this->getString('channel', '');
            App::add($this->adminId, $appType, $appTitle, $appV, $appDesc, $url, $upgradeWay, $dialogRepeat, $channel);
            $this->json(true, '添加成功');
        } else {
            $this->assign('channelDict', App::$AndroidChannelDict);
        }
    }

    /**
     * 编辑应用。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id           = $this->getInt('id');
            $appType      = $this->getInt('app_type');
            $appTitle     = $this->getString('app_title');
            $appV         = $this->getString('app_v');
            $appDesc      = $this->getString('app_desc');
            $url          = $this->getString('url');
            $upgradeWay   = $this->getString('upgrade_way');
            $dialogRepeat = $this->getInt('dialog_repeat');
            $channel      = $this->getString('channel', '');
            App::edit($this->adminId, $id, $appType, $appTitle, $appV, $appDesc, $url, $upgradeWay, $dialogRepeat, $channel);
            $this->json(true, '修改成功');
        } else {
            $id     = $this->getInt('id');
            $detail = App::detail($id);
            $this->assign('detail', $detail);
            $this->assign('channelDict', App::$AndroidChannelDict);
        }
    }

    /**
     * 删除应用。
     * 
     * @return void
     */
    public function deleteAction()
    {
        $id = $this->getInt('id');
        App::delete($this->adminId, $id);
        $this->json(true, '删除成功');
    }
}