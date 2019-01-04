<?php
/**
 * 广告表。
 * @author fingerQin
 * @date 2018-08-07
 */

namespace Models;

use finger\Database\Db;
use Utils\YCore;
use Utils\YDate;

class Ad extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'finger_ad';

    /**
     * 获取指定广告位置的广告列表。
     *
     * @param  int     $posId    广告位置ID。
     * @param  string  $adName   广告名称。模糊搜索广告名称。
     * @param  string  $display  显示状态：-1全部、1显示、0隐藏。
     * @param  int     $page     页码。
     * @param  int     $count    每页显示条数。
     * @return array
     */
    public function getList($posId, $adName = '', $display = -1, $page = 1, $count = 20)
    {
        $offset  = $this->getPaginationOffset($page, $count);
        $columns = ' * ';
        $where   = ' WHERE status = :status AND pos_id = :pos_id ';
        $params  = [
            ':status' => self::STATUS_YES,
            ':pos_id' => $posId
        ];
        if (strlen($adName) > 0) {
            $where .= ' AND ad_name LIKE :ad_name ';
            $params[':ad_name'] = "%{$adName}%";
        }
        if ($display != -1) {
            $where .= ' AND display LIKE :display ';
            $params[':display'] = $display;
        }
        $orderBy   = ' ORDER BY listorder ASC, ad_id DESC ';
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
            'isnext' => $this->IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 设置广告排序值。
     *
     * @param  int    $adId     广告ID。
     * @param  array  $sortVal  排序值。
     * @return bool
     */
    public function sortAd($adId, $sortVal)
    {
        $data = [
            'listorder' => $sortVal
        ];
        $where = [
            'ad_id' => $adId
        ];
        return $this->update($data, $where);
    }
}