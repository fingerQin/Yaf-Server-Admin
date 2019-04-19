<?php
/**
 * API 应用密钥管理。
 * @author fingerQin
 * @date 2018-07-10
 */

use Utils\YCore;
use finger\Paginator;
use Services\System\Notice;

class NoticeController extends \Common\controllers\Admin
{
    /**
     * 公告列表。
     */
    public function listsAction()
    {
        $title     = $this->getString('title', '');
        $status    = $this->getInt('status', -1);
        $page      = $this->getInt('page', 1);
        $result    = Notice::lists($title, $status, $page, 20);
        $Paginator = new Paginator($result['total'], 20);
        $pageHtml  = $Paginator->shopPageShow();
        $this->assign('list', $result['list']);
        $this->assign('pageHtml', $pageHtml);
        $this->assign('status', $status);
        $this->assign('title', $title);
    }

    /**
     * 状态更新。
     */
    public function statusAction()
    {
        $noticeid = $this->getInt('noticeid');
        $status   = $this->getInt('status');
        Notice::statusUpdate($this->adminId, $noticeid, $status);
        $this->json(true, '操作成功');
    }

    /**
     * 添加公告。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $title    = $this->getString('title', '');
            $summary  = $this->getString('summary', '');
            $body     = $this->getString('body', '');
            $terminal = $this->getString('terminal', '');
            Notice::add($this->adminId, $title, $summary, $body, $terminal);
            $this->json(true, '添加成功');
        }
        $this->assign('terminal', \Models\Notice::$terminalDict);
    }

    /**
     * 更新公告。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $noticeid = $this->getInt('noticeid');
            $title    = $this->getString('title', '');
            $summary  = $this->getString('summary', '');
            $body     = $this->getString('body', '');
            $terminal = $this->getString('terminal', '');
            Notice::edit($this->adminId, $noticeid, $title, $summary, $body, $terminal);
            $this->json(true, '更新成功');
        }
        $noticeid = $this->getInt('noticeid');
        $detail   = Notice::detail($noticeid);
        $this->assign('detail', $detail);
        $this->assign('terminal', \Models\Notice::$terminalDict);
    }
}