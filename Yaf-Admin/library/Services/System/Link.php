<?php
/**
 * 友情链接管理。
 * @author fingerQin
 * @date 2016-03-29
 */

namespace Services\System;

use finger\Validator;
use finger\Database\Db;
use Utils\YCore;
use Models\Link as LinkModel;

class Link extends \Services\AbstractBase
{
    /**
     * 友情链接列表。
     *
     * @param  string  $keyword  友情链接名称查询关键词。
     * @param  int     $catId    分类ID。
     * @param  int     $page     当前页码。
     * @param  int     $count    每页显示记录条数。
     * @return array
     */
    public static function list($keyword = '', $catId = -1, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $table     = ' FROM finger_link AS a INNER JOIN finger_category AS b ON(a.cat_id = b.cat_id) ';
        $columns   = ' a.*,b.cat_name ';
        $where     = ' WHERE a.status = :status ';
        $params    = [
            ':status' => LinkModel::STATUS_YES
        ];
        if (strlen($keyword) > 0) {
            $where .= ' AND a.link_name LIKE :link_name ';
            $params[':link_name'] = "%{$keyword}%";
        }
        if ($catId != -1) {
            $where .= ' AND b.cat_id LIKE :cat_id ';
            $params[':cat_id'] = $catId;
        }
        $orderBy   = ' ORDER BY a.link_id DESC ';
        $sql       = "SELECT COUNT(1) AS count {$table} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$table} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        $result    = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 获取友情链接详情。
     *
     * @param  int  $linkId  友情链接ID。
     * @return array
     */
    public static function detail($linkId)
    {
        $LinkModel = new LinkModel();
        $data = $LinkModel->fetchOne([], ['link_id' => $linkId, 'status' => LinkModel::STATUS_YES]);
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '友情链接不存在或已经删除');
        }
        return $data;
    }

    /**
     * 添加友情链接。
     *
     * @param  int     $adminId   管理员ID。
     * @param  string  $linkName  友情链接名称。
     * @param  string  $linkUrl   友情链接URL。
     * @param  int     $catId     分类ID。
     * @param  string  $imageUrl  友情链接图片。
     * @param  int     $display   显示状态：1显示、0隐藏。
     * @return void
     */
    public static function add($adminId, $linkName, $linkUrl, $catId, $imageUrl, $display = 1)
    {
        $LinkModel = new LinkModel();
        $data = [
            'link_name' => $linkName,
            'link_url'  => $linkUrl,
            'cat_id'    => $catId,
            'image_url' => $imageUrl,
            'display'   => $display
        ];
        $rules = [
            'link_name' => '友情链接名称|require|len:1:20:1',
            'link_url'  => '友情链接URL|require|len:1:100:1|url',
            'cat_id'    => '友情链接分类|require|integer',
            'image_url' => '友情链接图片|len:1:100:1',
            'display'   => '显示状态|require|integer'
        ];
        Validator::valido($data, $rules);
        $data['status'] = LinkModel::STATUS_YES;
        $data['c_by']   = $adminId;
        $data['c_time'] = date('Y-m-d H:i:s', time());
        $ok = $LinkModel->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 编辑友情链接。
     *
     * @param  int     $adminId   管理员ID。
     * @param  int     $linkId    友情链接ID。
     * @param  string  $linkName  友情链接名称。
     * @param  string  $linkUrl   友情链接URL。
     * @param  int     $catId     分类ID。
     * @param  string  $imageUrl  友情链接图片。
     * @param  int     $display   显示状态：1显示、0隐藏。
     * @return void
     */
    public static function edit($adminId, $linkId, $linkName, $linkUrl, $catId, $imageUrl, $display = 1)
    {
        $LinkModel = new LinkModel();
        $where = [
            'link_id' => $linkId,
            'status'  => LinkModel::STATUS_YES
        ];
        $linkDetail = $LinkModel->fetchOne([], $where);
        if (empty($linkDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '友情链接不存在或已经删除');
        }
        $data = [
            'link_name' => $linkName,
            'link_url'  => $linkUrl,
            'cat_id'    => $catId,
            'image_url' => $imageUrl,
            'display'   => $display
        ];
        $rules = [
            'link_name' => '友情链接名称|require|len:1:20:1',
            'link_url'  => '友情链接URL|require|len:1:100:1|url',
            'cat_id'    => '友情链接分类|require|integer',
            'image_url' => '友情链接图片|len:1:100:1',
            'display'   => '显示状态|require|integer'
        ];
        Validator::valido($data, $rules);
        $data['u_by']   = $adminId;
        $data['u_time'] = date('Y-m-d H:i:s', time());
        $ok = $LinkModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除友情链接。
     *
     * @param  int  $adminId  管理员ID。
     * @param  int  $linkId   友情链接ID。
     * @return void
     */
    public static function delete($adminId, $linkId)
    {
        $LinkModel = new LinkModel();
        $where = [
            'link_id' => $linkId,
            'status'  => LinkModel::STATUS_YES
        ];
        $linkDetail = $LinkModel->fetchOne([], $where);
        if (empty($linkDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '友情链接不存在或已经删除');
        }
        $data = [
            'status' => LinkModel::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $ok = $LinkModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 友情链接排序。
     *
     * @param  array $listorders 分类排序数据。[ ['友情链接ID' => '排序值'], ...... ]
     * @return bool
     */
    public static function sort($listorders)
    {
        if (empty($listorders)) {
            return;
        }
        foreach ($listorders as $linkId => $sortval) {
            $LinkModel = new LinkModel();
            $ok = $LinkModel->sortLink($linkId, $sortval);
            if (!$ok) {
                return YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
            }
        }
    }
}