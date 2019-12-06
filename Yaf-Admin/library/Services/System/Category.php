<?php
/**
 * 分类管理。
 * @author fingerQin
 * @date 2016-07-08
 */

namespace Services\System;

use finger\Core;
use finger\Database\Db;
use Models\News;
use Models\Category as CategoryModel;

class Category extends \Services\AbstractBase
{
	const CAT_NEWS  = 1;    // 文章分类。
    const CAT_LINK  = 2;    // 友情链接分类。
    const CAT_GOODS = 3;    // 商品分类。

    /**
     * 分类类型。
     *
     * @var array
     */
    public static $categoryTypeList = [
        self::CAT_NEWS  => '文章分类',
        self::CAT_LINK  => '友情链接',
        self::CAT_GOODS => '商品分类'
    ];

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
            Core::exception(STATUS_SERVER_ERROR, '对应的分类不存在');
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
    public static function list($parentid = 0, $catType = self::CAT_NEWS, $isFilter = false)
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
    public static function getByParentToCategory($parentid, $catType = self::CAT_NEWS, $isGetHide = true, $isFilter = false)
    {
        $allCategorys = self::getAllCategorys($catType, $isFilter);
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
    public static function getCategoryKeyValueList($parentid = 0, $catType = self::CAT_NEWS)
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
    public static function getCategoryCatCodeOrNameKeyValueList($parentid = 0, $catType = self::CAT_NEWS)
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
     * 添加分类。
     *
     * @param  int     $adminId   管理员ID。
     * @param  int     $catType   分类类型。
     * @param  string  $catName   分类名称。
     * @param  int     $parentid  父分类ID。
     * @param  int     $isOutUrl  是否外部链接。1是、0否。
     * @param  string  $outUrl    外部链接。
     * @param  int     $display   显示状态：1显示、0否。
     * @return void
     */
    public static function add($adminId, $catType, $catName, $parentid = 0, $isOutUrl = 0, $outUrl = '', $display = 1)
    {
        $CategoryModel = new CategoryModel();
        $lv = 1;
        if ($parentid != 0) {
            $parentCatInfo = $CategoryModel->fetchOne([], ['cat_id' => $parentid, 'status' => CategoryModel::STATUS_YES]);
            if (empty($parentCatInfo)) {
                Core::exception(STATUS_SERVER_ERROR, '父分类不存在或已经删除');
            }
            $lv = $parentCatInfo['lv'] + 1;
            // 当是添加子分类的时候。子分类的分类类型继续父分类的类型。
            $catType = $parentCatInfo['cat_type'];
        }
        $catCode = self::getNewCategoryCode($parentid);
        $data    = [
            'cat_name'   => $catName,
            'cat_type'   => $catType,
            'parentid'   => $parentid,
            'lv'         => $lv,
            'is_out_url' => $isOutUrl,
            'out_url'    => $outUrl,
            'display'    => $display,
            'cat_code'   => $catCode,
            'status'     => CategoryModel::STATUS_YES,
            'c_time'     => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
            'c_by'       => $adminId
        ];
        $ok = $CategoryModel->insert($data);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 编辑分类。
     *
     * @param  int     $adminId   管理员ID。
     * @param  int     $catId     分类ID。
     * @param  string  $catName   分类名称。
     * @param  int     $isOutUrl  是否外部链接。1是、0否。
     * @param  string  $outUrl    外部链接。
     * @param  int     $display   显示状态：1显示、0否。
     * @return void
     */
    public static function edit($adminId, $catId, $catName, $isOutUrl = 0, $outUrl = '', $display = 1)
    {
        $CategoryModel = new CategoryModel();
        $catInfo       = $CategoryModel->fetchOne([], ['cat_id' => $catId, 'status' => 1]);
        if (empty($catInfo)) {
            Core::exception(STATUS_SERVER_ERROR, '分类不存在或已经删除');
        }
        $data = [
            'cat_name'   => $catName,
            'is_out_url' => $isOutUrl,
            'out_url'    => $outUrl,
            'display'    => $display,
            'u_by'       => $adminId
        ];
        $where = [
            'cat_id' => $catId
        ];
        $ok = $CategoryModel->update($data, $where);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除分类。
     *
     * @param  int  $adminId  管理员ID。
     * @param  int  $catId    分类ID。
     * @return void
     */
    public static function delete($adminId, $catId)
    {
        // [1]
        $CategoryModel = new CategoryModel();
        $catInfo       = $CategoryModel->fetchOne([], ['cat_id' => $catId, 'status' => CategoryModel::STATUS_YES]);
        if (empty($catInfo)) {
            Core::exception(STATUS_SERVER_ERROR, '分类不存在或已经删除');
        }
        // [2] 目前只检查文章与友情链接，后续如果关联了其他功能，这里要做适当调整。
        $NewsModel = new News();
        $newsCount = $NewsModel->count(['cat_code' => $catInfo['cat_code'], 'status' => News::STATUS_YES]);
        if ($newsCount > 0) {
            Core::exception(STATUS_SERVER_ERROR, '请先清空该分类下的文章');
        }
        $where = [
            'cat_id' => $catId
        ];
        $data = [
            'status' => CategoryModel::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $ok = $CategoryModel->update($data, $where);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 获取分类详情。
     *
     * @param  int   $catId   分类ID。
     * @return array
     */
    public static function detail($catId)
    {
        $CategoryModel = new CategoryModel();
        $data = $CategoryModel->fetchOne([], ['cat_id' => $catId, 'status' => CategoryModel::STATUS_YES]);
        if (empty($data)) {
            Core::exception(STATUS_SERVER_ERROR, '分类不存在或已经删除');
        }
        return $data;
    }

    /**
     * 分类排序。
     *
     * @param  array  $listorders 分类排序数据。[ ['分类ID' => '排序值'], ...... ]
     * @return void
     */
    public static function sort($listorders)
    {
        if (empty($listorders)) {
            return;
        }
        $CategoryModel = new CategoryModel();
        foreach ($listorders as $catId => $sortVal) {
            $ok = $CategoryModel->sort($catId, $sortVal);
            if (!$ok) {
                return Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
            }
        }
    }

    /**
     * 获取分类编码的有效前缀。
     *
     * @param  string   $catCode  分类编码。
     * @param  int      $lv       分类层级。
     * @return string
     */
    public static function getCatCodePrefix($catCode, $lv)
    {
        return substr($catCode, 0, $lv * 3);
    }

    /**
     * 获取父分类下子分类最新 code 编码。
     * -- 1、cat_code编码最大允许的层级是10级。
     * -- 2、每级用3个数字点位表示。10级就是30位。
     * -- 3、第一级点位是100，补齐30位，则就变成了29个0.
     *
     * @param  int  $parentid  父分类ID。
     * @return string
     */
    public static function getNewCategoryCode($parentid)
    {
        $CategoryModel = new CategoryModel();
        if ($parentid == 0) {
            $sql    = 'SELECT * FROM finger_category WHERE parentid = :parentid ORDER BY cat_code DESC LIMIT 1';
            $params = [
                ':parentid' => $parentid
            ];
            $data = Db::one($sql, $params);
            if ($data) {
                $subCode    = substr($data['cat_code'], 0, 3);
                $subCode    = $subCode + 1;
                $subCatCode = sprintf("%-030s", $subCode);
            } else {
                $subCatCode = sprintf("%-030s", 100);
            }
        } else {
            $catInfo = $CategoryModel->fetchOne([], ['cat_id' => $parentid, 'status' => CategoryModel::STATUS_YES]);
            if (empty($catInfo)) {
                Core::exception(STATUS_SERVER_ERROR, '父分类不存在或已经删除');
            }
            $codePrefix = substr($catInfo['cat_code'], 0, $catInfo['lv'] * 3);
            $sql        = 'SELECT * FROM finger_category WHERE cat_code LIKE :cat_code ORDER BY cat_code DESC LIMIT 1';
            $params     = [
                ':cat_code' => "{$codePrefix}%"
            ];
            $data       = Db::one($sql, $params);
            $subCode    = substr($data['cat_code'], $catInfo['lv'] * 3, 3);
            $subCode    = $subCode + 1;
            $subCatCode = sprintf("%-030s", "{$codePrefix}{$subCode}");
        }
        return $subCatCode;
    }

    /**
     * 获取全部分类。
     *
     * @param  int  $catType   分类类型。
     * @param  bool $isFilter  是否过滤无用字段。
     * 
     * @return array
     */
    protected static function getAllCategorys($catType = self::CAT_NEWS, $isFilter = false)
    {
        $cacheKey = 'Yaf-Admin-all-categorys';
        if (\Yaf_Registry::has($cacheKey)) {
            return \Yaf_Registry::get($cacheKey);
        } else {
            $where = [
                'status'   => CategoryModel::STATUS_YES,
                'cat_type' => $catType
            ];
            $columns       = ['cat_id', 'cat_name', 'parentid', 'lv', 'cat_code', 'cat_type', 'display', 'is_out_url', 'out_url', 'listorder', 'tpl_name'];
            $CategoryModel = new CategoryModel();
            $result        = $CategoryModel->fetchAll($columns, $where);
            \Yaf_Registry::set($cacheKey, $result);
            return $result;
        }
    }
}