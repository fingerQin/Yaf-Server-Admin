<?php
/**
 * 短信模板管理。
 * @author fingerQin
 * @date 2019-04-17
 */

use finger\Paginator;
use Services\Sms\Tpl;

class SmsTplController extends \Common\controllers\Admin
{
    /**
     * 短信模板列表。
     */
    public function listsAction()
    {
        $sendKey   = $this->getString('send_key', '');
        $page      = $this->getInt('page', 1);
        $result    = Tpl::lists($sendKey, $page, 20);
        $paginator = new Paginator($result['total'], 20);
        $pageHtml  = $paginator->pageShow();
        $this->assign('send_key', $sendKey);
        $this->assign('list', $result['list']);
        $this->assign('pageHtml', $pageHtml);
    }

    /**
     * 短信模板添加。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $sendKey     = $this->getString('send_key', '');
            $title       = $this->getString('title', '');
            $smsBody     = $this->getString('sms_body', '');
            $triggerType = $this->getInt('trigger_type');
            Tpl::add($this->adminId, $sendKey, $title, $smsBody, $triggerType);
            $this->json(true, '添加成功');
        }
    }

    /**
     * 短信模板编辑。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id          = $this->getInt('id');
            $sendKey     = $this->getString('send_key', '');
            $title       = $this->getString('title', '');
            $smsBody     = $this->getString('sms_body', '');
            $triggerType = $this->getInt('trigger_type');
            Tpl::edit($this->adminId, $id, $sendKey, $title, $smsBody, $triggerType);
            $this->json(true, '更新成功');
        } else {
            $id = $this->getInt('id');
            $detail = Tpl::detail($id);
            $this->assign('detail', $detail);
        }
    }
}