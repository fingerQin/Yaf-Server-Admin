<?php
/**
 * 管理员。
 * @author fingerQin
 * @date 2018-07-07
 */

use finger\Paginator;
use Services\Power\AdminUser;
use Services\Power\Role;

class AdminController extends \Common\controllers\Admin
{
    /**
     * 管理员列表。
     */
    public function indexAction()
    {
        $keywords  = $this->getString('keywords', '');
        $page      = $this->getString('page', 1);
        $result    = AdminUser::list($keywords, $page, 10);
        $paginator = new Paginator($result['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('page_html', $pageHtml);
        $this->assign('keywords', $keywords);
        $this->assign('list', $result['list']);
    }

    /**
     * 添加管理员。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $realname    = $this->getString('realname');
            $password    = $this->getString('password');
            $mobilephone = $this->getString('mobilephone');
            $roleid      = $this->getInt('roleid');
            AdminUser::add($this->adminId, $realname, $password, $mobilephone, $roleid);
            $this->json(true, '添加成功');
        } else {
            $roles = Role::list();
            $this->assign('roles', $roles);
        }
    }

    /**
     * 禁用/解禁账号。
     */
    public function forbidAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $adminId = $this->getInt('admin_id');
            $status  = $this->getInt('status');
            AdminUser::forbid($this->adminId, $adminId, $status);
            $this->json(true, '操作成功');
        } else {
            $roles = Role::list();
            $this->assign('roles', $roles);
        }
    }

    /**
     * 编辑管理员。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $adminId     = $this->getInt('admin_id');
            $realname    = $this->getString('realname');
            $password    = $this->getString('password', '');
            $mobilephone = $this->getString('mobilephone');
            $roleid      = $this->getInt('roleid');
            AdminUser::edit($this->adminId, $adminId, $realname, $mobilephone, $roleid, $password);
            $this->json(true, '修改成功');
        } else {
            $adminId     = $this->getInt('admin_id');
            $adminDetail = AdminUser::detail($adminId);
            $roles       = Role::list();
            $this->assign('detail', $adminDetail);
            $this->assign('roles', $roles);
        }
    }

    /**
     * 删除管理员。
     */
    public function deleteAction()
    {
        $adminId = $this->getInt('admin_id');
        AdminUser::delete($this->adminId, $adminId);
        $this->json(true, '删除成功');
        $this->end();
    }

    /**
     * 管理员修改个人密码。
     */
    public function editPwdAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $oldPwd = $this->getString('old_pwd');
            $newPwd = $this->getString('new_pwd');
            AdminUser::editPwd($this->adminId, $oldPwd, $newPwd);
            $this->json(true, '修改成功');
        } else {
            $adminInfo = AdminUser::detail($this->adminId);
            $this->assign('admin_info', $adminInfo);
        }
    }

    /**
     * 登录历史。
     */
    public function loginHistoryAction()
    {
        $page      = $this->getString('page', 1);
        $result    = AdminService::getAdminLoginHistoryList($this->adminId, $page, 20);
        $paginator = new Paginator($result['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('page_html', $pageHtml);
        $this->assign('list', $result['list']);
    }
}