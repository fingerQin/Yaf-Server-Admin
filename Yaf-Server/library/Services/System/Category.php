<?php
/**
 * 系统分类。
 * @author fingerQin
 * @date 2018-09-05
 */

namespace Services\System;

use finger\Utils\YCore;
use Models\Category as CategoryModel;

class Category extends \Services\AbstractBase
{
    /**
     * 按分类编码查询分类。
     *
     * @param  string  $catCode  分类编码。
     * @return array
     */
    public static function getCategoryByCatCode($catCode)
    {
        $CategoryModel = new CategoryModel();
        $catInfo = $CategoryModel->fetchOne([], ['cat_code' => $catCode]);
        if (empty($catInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '文章对应的分类不存在');
        }
        return $catInfo;
    }

    /**
     * 获取分类列表。
     *
     * @param  int   $parentid  父ID。默认值0。
     * @param  int   $catType   分类类型。
     * @param  bool  $isFilter  是否过滤无用字段。
     * @return array
     */
    public static function list($parentid = 0, $catType = CategoryModel::CAT_NEWS, $isFilter = false)
    {
        $categoryList = self::getByParentToCategory($parentid, $catType, true, $isFilter);
        if (empty($categoryList)) {
            return $categoryList;
        } else {
            foreach ($categoryList as $key => $menu) {
                $categoryList[$key]['sub'] = self::list($menu['cat_id'], $catType, $isFilter);
            }
            return $categoryList;
        }
    }

    /**
     * 通过父分类ID读取子分类。
     *
     * @param  int   $parentid   父分类ID。
     * @param  int   $catType    分类类型。
     * @param  int   $isGetHide  是否获取隐藏的分类。
     * @param  bool  $isFilter   是否过滤无用字段。
     * @return array
     */
    public static function getByParentToCategory($parentid, $catType = CategoryModel::CAT_NEWS, $isGetHide = true, $isFilter = false)
    {
        $allCategorys = self::all($catType, $isFilter);
        $categorys    = [];
        foreach ($allCategorys as $category) {
            if ($isGetHide && $category['display'] == 0) {
                continue;
            }
            if ($category['parentid'] == $parentid && $category['cat_type'] == $catType) {
                $arrKey = "{$category['listorder']}_{$category['cat_id']}";
                if ($isFilter) {
                    $categorys[$arrKey] = [
                        'cat_id'   => $category['cat_id'],
                        'cat_name' => $category['cat_name'],
                        'parentid' => $category['parentid'],
                        'cat_code' => $category['cat_code'],
                    ];
                } else {
                    $categorys[$arrKey] = $category;
                }
            }
        }
        ksort($categorys);
        return $categorys;
    }

    /**
     * 以键值对儿模式返回分类列表。
     *
     * @param  int  $parentid  父ID。默认值0。
     * @param  int  $catType   分类类型。
     * @return array
     */
    public static function getCategoryKeyValueList($parentid = 0, $catType = CategoryModel::CAT_NEWS)
    {
        $list    = self::list($parentid, $catType);
        $newList = [];
        foreach ($list as $cat) {
            $newList[$cat['cat_id']] = $cat['cat_name'];
        }
        unset($list);
        return $newList;
    }

    /**
     * 以键值对儿模式返回分类列表。
     *
     * @param  int  $parentid  父ID。默认值0。
     * @param  int  $catType   分类类型。
     * @return array
     */
    public static function getCategoryCatCodeOrNameKeyValueList($parentid = 0, $catType = CategoryModel::CAT_NEWS)
    {
        $list    = self::list($parentid, $catType);
        $newList = [];
        foreach ($list as $cat) {
            $newList[$cat['cat_code']] = $cat['cat_name'];
        }
        unset($list);
        return $newList;
    }

    /**
     * 获取分类详情。
     *
     * @param  int  $catId  分类ID。
     * @return array
     */
    public static function detail($catId)
    {
        $CategoryModel = new CategoryModel();
        $data = $CategoryModel->fetchOne([], ['cat_id' => $catId, 'status' => CategoryModel::STATUS_YES]);
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '分类不存在或已经删除');
        }
        return $data;
    }

    /**
     * 获取全部分类。
     *
     * @param  int   $catType   分类类型。
     * @param  bool  $isFilter  是否过滤无用字段。
     * 
     * @return array
     */
    protected static function all($catType = self::CAT_NEWS, $isFilter = false)
    {
        $cacheKey = 'categorys';
        if (\Yaf_Registry::has($cacheKey)) {
            return \Yaf_Registry::get($cacheKey);
        } else {
            $where = [
                'status'   => CategoryModel::STATUS_YES,
                'cat_type' => $catType
            ];
            $columns       = ['cat_id', 'cat_name', 'parentid', 'lv', 'cat_code', 'cat_type', 
                              'display', 'is_out_url', 'out_url', 'listorder', 'tpl_name'];
            $CategoryModel = new CategoryModel();
            $result        = $CategoryModel->fetchAll($columns, $where);
            \Yaf_Registry::set($cacheKey, $result);
            return $result;
        }
    }
}