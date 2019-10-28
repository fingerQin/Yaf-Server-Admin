<?php
/**
 * 友情链接管理。
 * @author fingerQin
 * @date 2017-07-06
 */

use finger\Utils\YCore;
use finger\Utils\YUrl;
use finger\Paginator;
use Services\System\Category;
use Services\System\Link;

class LinkController extends \Common\controllers\Admin
{
    /**
     * 友情链接列表。
     */
    public function indexAction()
    {
        $keywords  = $this->getString('keywords', '');
        $catId     = $this->getString('cat_id', -1);
        $page      = $this->getInt('page', 1);
        $list      = Link::list($keywords, $catId, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('keywords', $keywords);
        $this->assign('cat_id', $catId);
        $this->assign('list', $list['list']);
    }

    /**
     * 友情链接添加。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $linkName = $this->getString('link_name');
            $linkUrl  = $this->getString('link_url');
            $catId    = $this->getInt('cat_id');
            $imageUrl = $this->getString('image_url');
            $display  = $this->getInt('display');
            Link::add($this->adminId, $linkName, $linkUrl, $catId, $imageUrl, $display);
            $this->json(true, '添加成功');
        } else {
            $list = Category::list(0, 2);
            if (empty($list)) {
                YCore::exception(STATUS_ERROR, '请立即创建友情链接分类');
            }
            $filesDomainName = YUrl::getFilesDomainName();
            $this->assign('cat_list', $list);
            $this->assign('files_domain_name', $filesDomainName);
        }
    }

    /**
     * 友情链接编辑。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $linkId   = $this->getInt('link_id');
            $linkName = $this->getString('link_name');
            $linkUrl  = $this->getString('link_url');
            $catId    = $this->getInt('cat_id');
            $imageUrl = $this->getString('image_url');
            $display  = $this->getInt('display');
            Link::edit($this->adminId, $linkId, $linkName, $linkUrl, $catId, $imageUrl, $display);
            $this->json(true, '修改成功');
        } else {
            $linkId = $this->getInt('link_id');
            $detail = Link::detail($linkId);
            $list   = Category::list(0, 2);
            $this->assign('detail', $detail);
            if (empty($list)) {
                YCore::exception(STATUS_ERROR, '请立即创建友情链接分类');
            }
            $filesDomainName = YUrl::getFilesDomainName();
            $this->assign('cat_list', $list);
            $this->assign('files_domain_name', $filesDomainName);
        }
    }

    /**
     * 友情链接删除。
     */
    public function deleteAction()
    {
        $linkId = $this->getInt('link_id');
        Link::delete($this->adminId, $linkId);
        $this->json(true, '删除成功');
    }

    /**
     * 友情链接排序。
     */
    public function sortAction()
    {
        if ($this->_request->isPost()) {
            $listorders = $this->getArray('listorders');
            Link::sort($listorders);
            $this->json(true, '排序成功');
        }
    }
}