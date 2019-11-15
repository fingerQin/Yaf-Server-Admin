<?php
/**
 * 文章分类管理。
 * @author fingerQin
 * @date 2018-07-08
 */

use finger\Utils\YCore;
use Services\System\Category;

class CategoryController extends \Common\controllers\Admin
{
    /**
     * 分类列表。
     */
    public function indexAction()
    {
        $catType = $this->getInt('cat_type', Category::CAT_NEWS);
        $list    = Category::list(0, $catType);
        $this->assign('list', $list);
        $this->assign('cat_type', $catType);
        $this->assign('cat_type_list', Category::$categoryTypeList);
    }

    /**
     * 添加分类。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $catType  = $this->getInt('cat_type', -1);
            $catName  = $this->getString('cat_name');
            $parentid = $this->getInt('parentid');
            $isOutUrl = $this->getInt('is_out_url');
            $outUrl   = $this->getString('out_url');
            $display  = $this->getInt('display');
            Category::add($this->adminId, $catType, $catName, $parentid, $isOutUrl, $outUrl, $display);
            $this->json(true, '操作成功');
        } else {
            $catType  = $this->getInt('cat_type', Category::CAT_NEWS);
            $parentid = $this->getInt('parentid', 0);
            $parentCatInfo = [];
            if ($parentid > 0) {
                $parentCatInfo = Category::detail($parentid);
                $catType = $parentCatInfo['cat_type'];
            }
            $list = Category::list(0);
            $this->assign('parentid', $parentid);
            $this->assign('cat_type', $catType);
            $this->assign('list', $list);
            $this->assign('parent_cat_info', $parentCatInfo);
            $this->assign('cat_type_list', Category::$categoryTypeList);
        }
    }

    /**
     * 编辑分类。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $catId    = $this->getInt('cat_id');
            $catName  = $this->getString('cat_name');
            $isOutUrl = $this->getInt('is_out_url');
            $outUrl   = $this->getString('out_url');
            $display  = $this->getInt('display');
            Category::edit($this->adminId, $catId, $catName, $isOutUrl, $outUrl, $display);
            $this->json(true, '操作成功');
        } else {
            $parentid = $this->getInt('parentid', 0);
            $catId    = $this->getInt('cat_id');
            $detail   = Category::detail($catId);
            $list     = Category::list(0);
            $this->assign('parentid', $parentid);
            $this->assign('detail', $detail);
            $this->assign('list', $list);
            $this->assign('cat_type_list', Category::$categoryTypeList);
        }
    }

    /**
     * 删除分类。
     */
    public function deleteAction()
    {
        $catId = $this->getInt('cat_id');
        Category::delete($this->adminId, $catId);
        $this->json(true, '删除成功');
    }

    /**
     * 分类排序。
     */
    public function sortAction()
    {
        if ($this->_request->isPost()) {
            $listorders = $this->getArray('listorders');
            Category::sort($listorders);
            $this->json(true, '排序成功');
        }
    }

    /**
     * 根据分类 ID 获取子分类列表并以 JSON 格式返回。
     * 
     * @return void
     */
    public function getListJsonAction()
    {
        if ($this->_request->isPost()) {
            $catId   = $this->getInt('cat_id', 0);
            $catType = $this->getInt('cat_type');
            $catList = Category::list($catId, $catType, true, true);
            $this->json(true, 'success', $catList);
        }
    }
}