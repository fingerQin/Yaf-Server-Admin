<?php
/**
 * API 应用密钥管理。
 * @author fingerQin
 * @date 2018-07-10
 */

use finger\Paginator;
use Utils\YCore;
use Services\System\ApiAuth;

class ApiController extends \Common\controllers\Admin
{
    /**
     * 应用列表。
     */
    public function listAction()
    {
        $page      = $this->getInt(YCore::appconfig('pager'), 1);
        $list      = ApiAuth::list($page, 10);
        $paginator = new Paginator($list['total'], 10);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('page_html', $pageHtml);
        $this->assign('list', $list['list']);
    }

    /**
     * 添加应用。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $apiType   = $this->getString('api_type');
            $apiName   = $this->getString('api_name');
            $apiKey    = $this->getString('api_key');
            $apiSecret = $this->getString('api_secret');
            ApiAuth::add($this->adminId, $apiType, $apiName, $apiKey, $apiSecret);
            $this->json(true, '添加成功');
        }
    }

    /**
     * 编辑应用。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id        = $this->getInt('id');
            $apiType   = $this->getString('api_type');
            $apiName   = $this->getString('api_name');
            $apiKey    = $this->getString('api_key');
            $apiSecret = $this->getString('api_secret');
            ApiAuth::edit($this->adminId, $id, $apiType, $apiName, $apiKey, $apiSecret);
            $this->json(true, '修改成功');
        }
        $id = $this->getInt('id');
        $detail = ApiAuth::detail($id);
        $this->assign('detail', $detail);
    }

    /**
     * 删除应用。
     */
    public function deleteAction()
    {
        $id = $this->getInt('id');
        ApiAuth::delete($this->adminId, $id);
        $this->json(true, '删除成功');
    }
}