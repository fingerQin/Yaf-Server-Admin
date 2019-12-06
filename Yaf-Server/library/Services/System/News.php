<?php
/**
 * 文章模块。
 * @author fingerQin
 * @date 2019-08-21
 */

namespace Services\System;

use finger\Core;
use finger\Database\Db;
use Models\News as NewsModel;

class News extends \Services\AbstractBase
{
    /**
     * 文章列表。
     *
     * @param  int  $catId  分类 ID。
     * @param  int  $page   页码。
     * @param  int  $count  每页显示条数。
     *
     * @return array
     */
    public static function lists($catId = -1, $page = 1, $count = 20)
    {
        $from    = ' FROM finger_news ';
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' news_id,title,intro,image_url,source,c_time ';
        $where   = ' WHERE status = :status AND display = :display ';
        $params  = [
            ':status'  => NewsModel::STATUS_YES,
            ':display' => NewsModel::STATUS_YES
        ];
        $orderBy = ' ORDER BY news_id DESC ';
        $sql     = "SELECT COUNT(1) AS count {$from} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$from} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        $result  = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 文章详情。
     *
     * @param  int  $newsId  文章 ID。
     *
     * @return array
     */
    public static function detail($newsId)
    {
        $columns = 'a.news_id,a.title,a.intro,a.image_url,a.source,a.c_time,b.content';
        $sql = "SELECT {$columns} FROM finger_news AS a LEFT JOIN finger_news_data AS b ON(a.news_id = b.news_id) "
             . "WHERE a.news_id = :news_id AND status = :status AND a.display = :display LIMIT 1";
        $params   = [
            ':news_id' => $newsId,
            ':status'  => NewsModel::STATUS_YES,
            ':display' => NewsModel::STATUS_YES
        ];
        $detail = Db::one($sql, $params);
        if (empty($detail)) {
            Core::exception(STATUS_SERVER_ERROR, '文章不存在或已经删除');
        }
        return $detail;
    }
}