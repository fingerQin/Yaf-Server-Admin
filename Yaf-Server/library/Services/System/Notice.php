<?php
/**
 * 系统公告。
 * @author fingerQin
 * @date 2019-04-19
 */

namespace Services\System;

use Models\Notice as NoticeModel;

class Notice extends \Services\AbstractBase
{
    /**
     * 用户消息列表。
     *
     * @param  int  $page    页码。
     * @param  int  $count   每页显示条数。
     *
     * @return array
     */
    public static function lists($page = 1, $count = 20)
    {
        $from    = ' FROM tb_notice ';
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' * ';
        $where   = ' WHERE userid = :userid AND status = :status ';
        $params  = [
            ':cur_status' => NoticeModel::STATUS_YES
        ];
        $orderBy = ' ORDER BY noticeid DESC ';
        $sql     = "SELECT COUNT(1) AS count {$from} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$from} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        $result  = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }
}