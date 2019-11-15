<?php
/**
 * 告警管理。
 * @author fingerQin
 * @date 2019-06-18
 */

use finger\Paginator;
use Services\System\Monitor as MonitorService;

class MonitorController extends \Common\controllers\Admin
{
    /**
     * 告警列表。
     */
    public function listsAction()
    {
        $code      = $this->getString('code', '');
        $page      = $this->getInt('page', 1);
        $startTime = $this->getString('start_time', '');
        $endTime   = $this->getString('start_time', '');
        $result    = MonitorService::lists($code, $startTime, $endTime, $page, 20);
        $paginator = new Paginator($result['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('result', $result);
        $this->assign('code', $code);
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('codeDict', MonitorService::codeDict());
    }

    /**
     * 告警详情。
     */
    public function detailAction()
    {
        $id     = $this->getInt('id');
        $detail = MonitorService::detail($id);
        $this->assign('detail', $detail);
    }

    /**
     * 已处理。
     */
    public function processedAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id     = $this->getInt('id');
            $remark = $this->getString('remark', '');
            MonitorService::processed($id, $remark);
            $this->json(true, '操作成功');
        } else {
            $id = $this->getInt('id');
            $this->assign('id', $id);
        }
    }
}