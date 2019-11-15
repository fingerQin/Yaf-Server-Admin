<?php
/**
 * 文章管理。
 * @author fingerQin
 * @date 2018-07-11
 */

use finger\Utils\YUrl;
use finger\Paginator;
use Services\System\Category;
use Services\System\Upload;
use Services\System\News;

class NewsController extends \Common\controllers\Admin
{
    /**
     * 文章列表。
     */
    public function indexAction()
    {
        $title     = $this->getString('title', '');
        $adminName = $this->getString('admin_name', '');
        $catCode   = $this->getString('cat_code', '');
        $starttime = $this->getString('starttime', '');
        $endtime   = $this->getString('endtime', '');
        $page      = $this->getInt('page', 1);
        $list      = News::list($title, $adminName, $catCode, $starttime, $endtime, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('list', $list['list']);
        $this->assign('title', $title);
        $this->assign('starttime', $starttime);
        $this->assign('endtime', $endtime);
        $this->assign('admin_name', $adminName);
        $this->assign('cat_code', $catCode);
    }

    /**
     * 添加文章。
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $title    = $this->getString('title');
            $catCode  = $this->getString('cat_code');
            $intro    = $this->getString('intro');
            $keywords = $this->getString('keywords');
            $source   = $this->getString('source');
            $imageUrl = $this->getString('image_url');
            $content  = $this->getString('content');
            $display  = $this->getInt('display');
            News::add($this->adminId, $catCode, $title, $intro, $keywords, $source, $imageUrl, $content, $display);
            $this->json(true, '操作成功');
        } else {
            $newsCatList     = Category::list(0, Category::CAT_NEWS, true);
            $frontendUrl     = YUrl::getDomainName();
            $filesDomainName = YUrl::getFilesDomainName();
            $this->assign('files_domain_name', $filesDomainName);
            $this->assign('news_cat_list', $newsCatList);
            $this->assign('frontend_url', $frontendUrl);
        }
    }

    /**
     * 编辑文章。
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $newsId   = $this->getInt('news_id');
            $catCode  = $this->getString('cat_code');
            $title    = $this->getString('title');
            $intro    = $this->getString('intro');
            $keywords = $this->getString('keywords');
            $source   = $this->getString('source');
            $imageUrl = $this->getString('image_url');
            $content  = $this->getString('content');
            $display  = $this->getInt('display');
            News::edit($this->adminId, $newsId, $catCode, $title, $intro, $keywords, $source, $imageUrl, $content, $display);
            $this->json(true, '操作成功');
        } else {
            $newsId          = $this->getInt('news_id');
            $detail          = News::detail($newsId, true);
            $newsCatList     = Category::list(0, 1);
            $frontendUrl     = YUrl::getDomainName();
            $filesDomainName = YUrl::getFilesDomainName();
            $this->assign('files_domain_name', $filesDomainName);
            $this->assign('news_cat_list', $newsCatList);
            $this->assign('detail', $detail);
            $this->assign('frontend_url', $frontendUrl);
        }
    }

    /**
     * 文章删除。
     */
    public function deleteAction()
    {
        $newsId = $this->getInt('news_id');
        News::delete($this->adminId, $newsId);
        $this->json(true, '操作成功');
    }

    /**
     * 文章排序。
     */
    public function sortAction()
    {
        if ($this->_request->isPost()) {
            $listorders = $this->getArray('listorders');
            News::sort($this->adminId, $listorders);
            $this->json(true, '排序成功');
        }
    }

    /**
     * 图片文件上传。
     */
    public function uploadAction()
    {
        header("Access-Control-Allow-Origin: *");
        try {
            $uploadType = $this->getString('dir', 'image');
            if ($uploadType == 'file') {
                $result = Upload::uploadOtherFile(1, $this->adminId, 'files', 10, 'imgFile');
            } else { // 图片。
                $result = Upload::uploadImage(1, $this->adminId, 'news', 2, 'imgFile');
            }
            echo json_encode(['error' => 0, 'url' => $result['image_url']]);
        } catch (\Exception $e) {
            echo json_encode(['error' => 1, 'message' => $e->getMessage()]);
        }
        $this->end();
    }
}