<?php
/**
 * 广告位/广告管理。
 * @author fingerQin
 * @date 2018-08-07
 */

use finger\Url;
use finger\Paginator;
use Services\System\Ad;
use Common\controllers\Admin;

class AdController extends Admin
{
    /**
     * 广告位列表。
     */
    public function positionAction()
    {
        $keywords  = $this->getString('keywords', '');
        $page      = $this->getInt('page', 1);
        $list      = Ad::getAdPostionList($keywords, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->pageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('keywords', $keywords);
        $this->assign('list', $list['list']);
    }

    /**
     * 广告位添加。
     */
    public function addPositionAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $posName    = $this->getString('pos_name');
            $posCode    = $this->getString('pos_code');
            $posAdCount = $this->getInt('pos_ad_count');
            Ad::addAdPostion($this->adminId, $posName, $posCode, $posAdCount);
            $this->json(true, '添加成功');
        }
    }

    /**
     * 广告位编辑。
     */
    public function editPositionAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $posId      = $this->getInt('pos_id');
            $posName    = $this->getString('pos_name');
            $posCode    = $this->getString('pos_code');
            $posAdCount = $this->getInt('pos_ad_count');
            Ad::editAdPostion($this->adminId, $posId, $posName, $posCode, $posAdCount);
            $this->json(true, '操作成功');
        } else {
            $posId  = $this->getInt('pos_id');
            $detail = Ad::getAdPostionDetail($posId);
            $this->assign('detail', $detail);
        }
    }

    /**
     * 广告位删除。
     */
    public function deletePositionAction()
    {
        $posId = $this->getInt('pos_id');
        Ad::deleteAdPostion($this->adminId, $posId);
        $this->json(true, '删除成功');
    }

    /**
     * 广告列表。
     */
    public function adlistAction()
    {
        $posId     = $this->getInt('pos_id');
        $adName    = $this->getString('ad_name', '');
        $display   = $this->getInt('display', -1);
        $page      = $this->getInt('page', 1);
        $list      = Ad::getAdList($posId, $adName, $display, $page, 10);
        $paginator = new Paginator($list['total'], 10);
        $pageHtml  = $paginator->pageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('ad_name', $adName);
        $this->assign('display', $display);
        $this->assign('list', $list['list']);
        $this->assign('pos_id', $posId);
    }

    /**
     * 广告添加。
     */
    public function addAdAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $posId         = $this->getInt('pos_id');
            $adName        = $this->getString('ad_name');
            $startTime     = $this->getString('start_time');
            $endTime       = $this->getString('end_time');
            $display       = $this->getInt('display');
            $remark        = $this->getString('remark');
            $adImageUrl    = $this->getString('ad_image_url');
            $adIpxImageUrl = $this->getString('ad_ipx_image_url', '');
            $adUrl         = $this->getString('ad_url');
            $terminal      = $this->getArray('terminal', []);
            $flag          = $this->getArray('flag', []);
            Ad::addAd($this->adminId, $posId, $adName, $startTime, $endTime, $display, 
            $remark, $adImageUrl, $adIpxImageUrl, $adUrl, $terminal, $flag);
            $this->json(true, '添加成功');
        } else {
            $posId = $this->getInt('pos_id');
            $filesDomainName = Url::getFilesDomainName();
            $this->assign('terminalDict', Ad::getAdTerminalDict());
            $this->assign('flagDict', Ad::getAdFlagDict());
            $this->assign('files_domain_name', $filesDomainName);
            $this->assign('pos_id', $posId);
        }
    }

    /**
     * 广告编辑。
     */
    public function editAdAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $adId          = $this->getInt('ad_id');
            $adName        = $this->getString('ad_name');
            $startTime     = $this->getString('start_time');
            $endTime       = $this->getString('end_time');
            $display       = $this->getInt('display');
            $remark        = $this->getString('remark');
            $adImageUrl    = $this->getString('ad_image_url');
            $adIpxImageUrl = $this->getString('ad_ipx_image_url', '');
            $adUrl         = $this->getString('ad_url');
            $terminal      = $this->getArray('terminal', []);
            $flag          = $this->getArray('flag', []);
            Ad::editAd($this->adminId, $adId, $adName, $startTime, $endTime, $display, 
            $remark, $adImageUrl, $adIpxImageUrl, $adUrl, $terminal, $flag);
            $this->json(true, '修改成功');
        } else {
            $adId   = $this->getInt('ad_id');
            $detail = Ad::getAdDetail($adId);
            $filesDomainName = Url::getFilesDomainName();
            $this->assign('terminalDict', Ad::getAdTerminalDict());
            $this->assign('flagDict', Ad::getAdFlagDict());
            $this->assign('files_domain_name', $filesDomainName);
            $this->assign('detail', $detail);
        }
    }

    /**
     * 广告删除。
     */
    public function deleteAdAction()
    {
        $adId = $this->getInt('ad_id');
        Ad::deleteAd($this->adminId, $adId);
        $this->json(true, '删除成功');
    }

    /**
     * 广告位排序。
     */
    public function adSortAction()
    {
        if ($this->_request->isPost()) {
            $listorders = $this->getArray('listorders');
            Ad::sortAd($listorders);
            $this->json(true, '排序成功');
        }
    }
}