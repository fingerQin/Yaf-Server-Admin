<?php
/**
 * 公告管理。
 * @author fingerQin
 * @date 2019-04-18
 */

namespace Services\System;

use Utils\YDate;
use Utils\YCore;
use Models\Notice as NoticeModel;
use finger\Database\Db;
use finger\Validator;

class Notice extends \Services\AbstractBase
{
    /**
     * 公告列表。
     *
     * @param  int  $title   标题关键词。
     * @param  int  $status  状态。-1全部、0-隐藏、1-正常。
     * @param  int  $page    页码。
     * @param  int  $count   每页显示条数。
     *
     * @return array
     */
    public static function lists($title = '', $status = -1, $page = 1, $count = 20)
    {
        $from    = ' FROM tb_notice ';
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' * ';
        $where   = ' WHERE 1 ';
        $params  = [];
        if ($status != -1) {
            $where .= ' AND cur_status = :cur_status ';
            $params[':cur_status'] = $status;
        } else {
            $where .= ' AND cur_status != :cur_status ';
            $params[':cur_status'] = NoticeModel::STATUS_DELETED;
        }
        if (strlen($title) > 0) {
            $where .= ' AND title LIKE :title ';
            $params[':title'] = "%{$title}%";
        }
        $orderBy = ' ORDER BY noticeid DESC ';
        $sql     = "SELECT COUNT(1) AS count {$from} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$from} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        foreach ($list as $k => $val) {
            $val['u_time']         = YDate::formatDateTime($val['u_time']);
            $val['status_label']   = NoticeModel::$statusDict[$val['cur_status']];
            $val['terminal_label'] = self::terminalConvert($val['terminal']);
            $list[$k]              = $val;
        }
        $result = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 添加公告。
     *
     * @param  int     $adminId   管理员 ID。
     * @param  string  $title     公告标题。
     * @param  string  $summary   公告摘要。
     * @param  string  $body      公告内容。
     * @param  int     $terminal  所属终端。
     *
     * @return void
     */
    public static function add($adminId, $title, $summary, $body, $terminal)
    {
        $rules = [
            'title'    => '公告标题|require|len:1:64:1',
            'summary'  => '公告摘要|require|len:1:255:1',
            'body'     => '公告内容|require|len:1:50000:1',
            'terminal' => '所属终端|require|len:1:10:0'
        ];
        $data = [
            'title'    => $title,
            'summary'  => $summary,
            'body'     => $body,
            'terminal' => $terminal
        ];
        Validator::valido($data, $rules);
        if (array_key_exists($terminal, NoticeModel::$terminalDict)) {
            YCore::exception(STATUS_SERVER_ERROR, '所属终端值有误');
        }
        $datetime = date('Y-m-d H:i:s', time());
        $data['terminal']   = self::sumTerminal($terminal);
        $data['cur_status'] = NoticeModel::STATUS_YES;
        $data['c_time']     = $datetime;
        $data['u_time']     = $datetime;
        $data['c_by']       = $adminId;
        $data['u_by']       = $adminId;
        $NoticeModel = new NoticeModel();
        $status = $NoticeModel->insert($data);
        if (!$status) {
            YCore::exception(STATUS_SERVER_ERROR, '');
        }
    }

    /**
     * 添加公告。
     *
     * @param  int     $adminId   管理员 ID。
     * @param  int     $noticeid  公告 ID。
     * @param  string  $title     公告标题。
     * @param  string  $summary   公告摘要。
     * @param  string  $body      公告内容。
     * @param  int     $terminal  所属终端。
     *
     * @return void
     */
    public static function edit($adminId, $noticeid, $title, $summary, $body, $terminal)
    {
        $rules = [
            'title'    => '公告标题|require|len:1:64:1',
            'summary'  => '公告摘要|require|len:1:255:1',
            'body'     => '公告内容|require|len:1:50000:1',
            'terminal' => '所属终端|require|len:1:10:0'
        ];
        $data = [
            'title'    => $title,
            'summary'  => $summary,
            'body'     => $body,
            'terminal' => $terminal
        ];
        Validator::valido($data, $rules);
        if (array_key_exists($terminal, NoticeModel::$terminalDict)) {
            YCore::exception(STATUS_SERVER_ERROR, '所属终端值有误');
        }
        $NoticeModel = new NoticeModel();
        $notice = $NoticeModel->fetchOne([], ['noticeid' => $noticeid]);
        if (empty($notice) || $notice['cur_status'] == NoticeModel::STATUS_DELETED) {
            YCore::exception(STATUS_SERVER_ERROR, '公告不存在或已经删除');
        }
        $data['terminal']   = self::sumTerminal($terminal);
        $data['cur_status'] = NoticeModel::STATUS_YES;
        $data['u_time']     = date('Y-m-d H:i:s', time());
        $data['u_by']       = $adminId;
        $status = $NoticeModel->update($data, ['noticeid' => $noticeid]);
        if (!$status) {
            YCore::exception(STATUS_SERVER_ERROR, '更新失败');
        }
    }

    /**
     * 公告详情。
     *
     * @param  int  $noticeid  公告 ID。
     *
     * @return array
     */
    public static function detail($noticeid)
    {
        $NoticeModel = new NoticeModel();
        $columns     = ['noticeid', 'title', 'summary', 'body', 'terminal'];
        $detail      = $NoticeModel->fetchOne($columns, [
            'noticeid'   => $noticeid,
            'cur_status' => ['!=', NoticeModel::STATUS_DELETED]]
        );
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '公告不存在或已经删除');
        }
        $detail['terminal_str'] = self::splitTerminal($detail['terminal']);
        return $detail;
    }

    /**
     * 公告状态(显示/隐藏)更新。
     *
     * @param  int  $adminId   管理员 ID。
     * @param  int  $noticeid  公告 ID。
     * @param  int  $status    状态。1-显示、0-隐藏。
     *
     * @return void
     */
    public static function statusUpdate($adminId, $noticeid, $status)
    {
        $status      = ($status == NoticeModel::STATUS_YES) ?: NoticeModel::STATUS_INVALID;
        $NoticeModel = new NoticeModel();
        $notice      = $NoticeModel->fetchOne([], ['noticeid' => $noticeid]);
        if (empty($notice) || $notice['cur_status'] == NoticeModel::STATUS_DELETED) {
            YCore::exception(STATUS_SERVER_ERROR, '公告不存在或已经删除');
        }
        $data['cur_status'] = $status;
        $data['u_time']     = date('Y-m-d H:i:s', time());
        $data['u_by']       = $adminId;
        $status = $NoticeModel->update($data, ['noticeid' => $noticeid]);
        if (!$status) {
            YCore::exception(STATUS_SERVER_ERROR, '更新失败');
        }
    }

    /**
     * 所属终端值求和。
     *
     * @param  string  $strBit  所属终端值。格式：1,2,4
     *
     * @return int
     */
    private static function sumTerminal($strBit)
    {
        $arrSplit = strlen($strBit) > 0 ? explode(',', $strBit) : [];
        return array_sum($arrSplit);
    }

    /**
     * 拆分所属终端为原始值。
     *
     * @param  int  $bitVal  所属终端位值。
     *
     * @return string
     */
    private static function splitTerminal($bitVal)
    {
        $data = [];
        if (($bitVal & NoticeModel::TERMINAL_APP) == NoticeModel::TERMINAL_APP) {
            $data[] = NoticeModel::TERMINAL_APP;
        }
        if (($bitVal & NoticeModel::TERMINAL_M) == NoticeModel::TERMINAL_M) {
            $data[] = NoticeModel::TERMINAL_M;
        }
        if (($bitVal & NoticeModel::TERMINAL_PC) == NoticeModel::TERMINAL_PC) {
            $data[] = NoticeModel::TERMINAL_PC;
        }
        return $data ? implode(',', $data) : '';
    }

    /**
     * 终端位值解析。
     *
     * @param  int  $bitVal  位值复合值。
     *
     * @return string
     */
    private static function terminalConvert($bitVal)
    {
        $data = [];
        if (($bitVal & NoticeModel::TERMINAL_APP) == NoticeModel::TERMINAL_APP) {
            $data[] = NoticeModel::$terminalDict[NoticeModel::TERMINAL_APP];
        }
        if (($bitVal & NoticeModel::TERMINAL_M) == NoticeModel::TERMINAL_M) {
            $data[] = NoticeModel::$terminalDict[NoticeModel::TERMINAL_M];
        }
        if (($bitVal & NoticeModel::TERMINAL_PC) == NoticeModel::TERMINAL_PC) {
            $data[] = NoticeModel::$terminalDict[NoticeModel::TERMINAL_PC];
        }
        return $data ? implode(',', $data) : '-';
    }
}