<?php
/**
 * 用户管理。
 * @author fingerQin
 * @date 2018-08-14
 */

use finger\Paginator;
use Services\User\User;

class UserController extends \Common\controllers\Admin
{
    /**
     * 用户列表。
     */
    public function listsAction()
    {
        $mobile    = $this->getString('mobile', '');
        $starttime = $this->getString('start_time', date('2015-06-06 00:00:00'));
        $endtime   = $this->getString('end_time', date('Y-m-d H:i:s'));
        $page      = $this->getInt('page', 1);
        $list      = User::list($mobile, $starttime, $endtime, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('list', $list['list']);
        $this->assign('mobile', $mobile);
        $this->assign('start_time', $starttime);
        $this->assign('end_time', $endtime);
    }

    /**
     * 修改密码。
     */
    public function editPwdAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $userid   = $this->getInt('userid');
            $password = $this->getString('password');
            User::editPwd($userid, $password);
            $this->json(true, '操作成功');
        } else {
            $userid = $this->getInt('userid');
            $this->assign('userid', $userid);
        }
    }

    /**
     * 状态更改。
     */
    public function statusAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $userid = $this->getInt('userid');
            $status = $this->getInt('status');
            User::editStatus($userid, $status);
            $this->json(true, '操作成功');
        } else {
            $userid = $this->getInt('userid');
            $this->assign('userid', $userid);
        }
    }

    /**
     * 清除账号登录锁定。
     */
    public function clearAccountLoginLockAction()
    {
        $userid = $this->getInt('userid');
        User::clearAccountLoginErrorLock($userid);
        $this->json(true, '清除成功');
    }
}