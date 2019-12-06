<?php
/**
 * 告警管理。
 * @author fingerQin
 * @date 2019-06-18
 */

namespace Services\System;

use finger\Core;
use finger\Database\Db;
use Models\Monitor as MonitorModel;

class Monitor extends \Services\AbstractBase
{
    /**
     * 告警列表。
     *
     * @param  string  $code        告警 CODE 编码。
     * @param  string  $startTime   查询起始时间。
     * @param  string  $endTime     查询截止时间。
     * @param  int     $page        页码。
     * @param  int     $count       每页显示条数。
     *
     * @return array
     */
    public static function lists($code = '', $startTime = '', $endTime = '', $page = 1, $count = 20)
    {
        $fromTable = ' FROM finger_monitor ';
        $offset    = self::getPaginationOffset($page, $count);
        $columns   = ' id, serial_no, code, remark, status, c_time ';
        $where     = ' WHERE 1 ';
        $params    = [];
        if (strlen($code) > 0) {
            $where  .= ' AND code = :code ';
            $params[':code'] = $code;
        }
        if (strlen($startTime) != '') {
            $where  .= ' AND c_time = :startTime ';
            $params[':startTime'] = $startTime;
        }
        if (strlen($endTime) != '') {
            $where  .= ' AND c_time = :endTime ';
            $params[':endTime'] = $endTime;
        }
        $orderBy = ' ORDER BY id DESC ';
        $sql     = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        foreach ($list as $k => $val) {
            $val['status_label'] = MonitorModel::$statusDict[$val['status']];
            $val['code_label']   = MonitorModel::$codeDict[$val['code']];
            $list[$k]            = $val;
        }
        $result = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 已处理。
     *
     * @param  int     $id      记录 ID。
     * @param  string  $remark  操作备注。
     *
     * @return void
     */
    public static function processed($id, $remark = '')
    {
        $MonitorModel = new MonitorModel();
        $monitor      = $MonitorModel->fetchOne([], ['id' => $id]);
        if (empty($monitor)) {
            Core::exception(STATUS_SERVER_ERROR, '该告警记录不存在');
        }
        if ($monitor['status'] == MonitorModel::STATUS_PROCESSED) {
            Core::exception(STATUS_SERVER_ERROR, '已处理，请勿重复操作！');
        }
        $updata = [
            'status' => MonitorModel::STATUS_PROCESSED,
            'remark' => $remark
        ];
        $status = $MonitorModel->update($updata, ['id' => $id]);
        if (!$status) {
            Core::exception(STATUS_SERVER_ERROR, '处理失败');
        }
    }

    /**
     * 详情。
     *
     * @param  int  $id  告警 ID。
     *
     * @return array
     */
    public static function detail($id)
    {
        $MonitorModel = new MonitorModel();
        $monitor      = $MonitorModel->fetchOne([], ['id' => $id]);
        if (empty($monitor)) {
            Core::exception(STATUS_SERVER_ERROR, '该告警记录不存在');
        }
        $monitor['status_label'] = MonitorModel::$statusDict[$monitor['status']];
        $monitor['code_label']   = MonitorModel::$codeDict[$monitor['code']];
        return $monitor;
    }

    /**
     * 获取告警编码字典。
     *
     * @return array
     */
    public static function codeDict()
    {
        return MonitorModel::$codeDict;
    }
}