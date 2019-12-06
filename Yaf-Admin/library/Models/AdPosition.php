<?php
/**
 * 广告位置表。
 * @author fingerQin
 * @date 2018-08-07
 */

namespace Models;

use finger\Database\Db;

class AdPosition extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'finger_ad_position';

    /**
     * 获取广告位置列表。
     *
     * @param  string  $keyword  查询关键词。
     * @param  int     $page     页码。
     * @param  int     $count    每页显示条数。
     * @return array
     */
    public function getList($keyword = '', $page = 1, $count = 10)
    {
        $offset  = $this->getPaginationOffset($page, $count);
        $columns = ' * ';
        $where   = ' WHERE status = :status ';
        $params  = [
            ':status' => self::STATUS_YES
        ];
        if (strlen($keyword) > 0) {
            $where .= ' AND ( ctitle LIKE :ctitle OR cname LIKE :cname )';
            $params[':ctitle'] = "%{$keyword}%";
            $params[':cname'] = "%{$keyword}%";
        }
        $orderBy   = ' ORDER BY pos_id DESC ';
        $sql       = "SELECT COUNT(1) AS count FROM {$this->tableName} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} FROM {$this->tableName} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        $result = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => $this->isHasNextPage($total, $page, $count)
        ];
        return $result;
    }
}