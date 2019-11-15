<?php
/**
 * 系统消息管理。
 * @author fingerQin
 * @date 2019-04-18
 */

use finger\Paginator;
use Services\System\Message;

class MessageController extends \Common\controllers\Admin
{
    /**
     * 消息列表。
     */
    public function listsAction()
    {
        $mobile     = $this->getString('mobile', '');
        $readStatus = $this->getInt('read_status', -1);
        $page       = $this->getInt('page', 1);
        $result     = Message::lists($mobile, $readStatus, $page, 20);
        $Paginator  = new Paginator($result['total'], 20);
        $pageHtml   = $Paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('list', $result['list']);
        $this->assign('mobile', $mobile);
        $this->assign('read_status', $readStatus);
    }

    /**
     * 发送系统消息。
     */
    public function sendAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $msgType = $this->getInt('msg_type');
            $title   = $this->getString('title', '');
            $content = $this->getString('content', '');
            $mobile  = $this->getString('mobile', '');
            $url     = $this->getString('url', '');
            Message::add($msgType, $mobile, $title, $content, $url);
            $this->json(true, '发送成功');
        }
    }

    /**
     * 删除。
     */
    public function deleteAction()
    {
        $msgid = $this->getInt('msgid');
        Message::delete($this->adminId, $msgid);
        $this->json(true, '删除成功');
    }
}