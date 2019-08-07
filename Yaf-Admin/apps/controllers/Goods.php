<?php
/**
 * 金币商城之商品管理。
 * @author fingerQin
 * @date 2019-08-07
 */

use Utils\YUrl;
use finger\Paginator;
use Services\Mall\Goods;
use Services\System\Upload;
use Services\System\Category;

class GoodsController extends \Common\controllers\Admin
{
    /**
     * 商品列表。
     */
    public function listAction()
    {
        $catId      = $this->getString('cat_id', -1);
        $updown     = $this->getInt('updown', -1);
        $goodsName  = $this->getString('goods_name', '');
        $startPrice = $this->getString('start_price', '');
        $endPrice   = $this->getString('end_price', '');
        $deleteShow = $this->getInt('is_delete_show', 0);
        $page       = $this->getInt('page', 1);
        $list       = Goods::list($updown, $goodsName, $catId, $startPrice, $endPrice, $deleteShow, $page, 10);
        $catList    = Category::list(0, Category::CAT_GOODS);
        $paginator  = new Paginator($list['total'], 10);
        $pageHtml   = $paginator->backendPageShow();
        $this->assign('cat_list', $catList);
        $this->assign('page_html', $pageHtml);
        $this->assign('updown', $updown);
        $this->assign('goods_name', $goodsName);
        $this->assign('start_price', $startPrice);
        $this->assign('end_price', $endPrice);
        $this->assign('cat_id', $catId);
        $this->assign('is_delete_show', $deleteShow);
        $this->assign('list', $list['list']);
    }

    /**
     * 添加商品。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = [
                'user_id'     => $this->adminId,
                'goods_name'  => $this->getString('goods_name'),
                'cat_id'      => $this->getInt('cat_id'),
                'listorder'   => $this->getInt('listorder'),
                'description' => $this->getString('description'),
                'spec_val'    => $this->getArray('spec_val', []),
                'products'    => $this->getArray('products', []),
                'goods_album' => $this->getArray('goods_album', []),
                'marketable'  => $this->getInt('marketable', 0)
            ];
            Goods::add($data);
            $this->json(true, '添加成功');
        }
        $catList = Category::list(0, Category::CAT_GOODS);
        $filesDomainName  = YUrl::getFilesDomainName();
        $this->assign('files_domain_name', $filesDomainName);
        $this->assign('cat_list', $catList);
    }

    /**
     * 编辑商品。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = [
                'goods_id'    => $this->getInt('goods_id'),
                'user_id'     => $this->adminId,
                'goods_name'  => $this->getString('goods_name'),
                'cat_id'      => $this->getInt('cat_id'),
                'listorder'   => $this->getInt('listorder'),
                'description' => $this->getString('description'),
                'spec_val'    => $this->getArray('spec_val', []),
                'products'    => $this->getArray('products', []),
                'goods_album' => $this->getArray('goods_album', []),
                'marketable'  => $this->getInt('marketable', 0)
            ];
            Goods::edit($data);
            $this->json(true, '保存成功');
        }
        $goodsId         = $this->getInt('goods_id');
        $goodsDetail     = Goods::detail($goodsId);
        $catList         = Category::list(0, Category::CAT_GOODS);
        $filesDomainName = YUrl::getFilesDomainName();
        $this->assign('files_domain_name', $filesDomainName);
        $this->assign('cat_list', $catList);
        $this->assign('data', $goodsDetail);
    }

    /**
     * 商品删除。
     */
    public function deleteAction()
    {
        $goodsId = $this->getInt('goods_id');
        Goods::delete($this->adminId, $goodsId);
        $this->json(true, '删除成功');
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
                $result = Upload::uploadOtherFile(1, $this->adminId, 'goodsfile', 10, 'imgFile');
            } else { // 图片。
                $result = Upload::uploadImage(1, $this->adminId, 'goodsimg', 2, 'imgFile');
            }
            echo json_encode(['error' => 0, 'url' => $result['image_url']]);
        } catch (\Exception $e) {
            echo json_encode(['error' => 1, 'message' => $e->getMessage()]);
        }
        $this->end();
    }
}