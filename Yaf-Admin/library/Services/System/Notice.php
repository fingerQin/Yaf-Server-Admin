<?php
/**
 * 公告管理。
 * @author fingerQin
 * @date 2019-04-18
 */

namespace Services\System;

use finger\Core;
use Models\Notice as NoticeModel;
use finger\Validator;
use finger\Database\Db;
use finger\Date;

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
        $from    = ' FROM finger_notice ';
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
            $val['u_time']         = Date::formatDateTime($val['u_time']);
            $val['status_label']   = NoticeModel::$statusDict[$val['cur_status']];
            $val['terminal_label'] = self::terminalConvert($val['terminal']);
            $list[$k]              = $val;
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
     * 添加公告。
     *
     * @param  int     $adminId        管理员 ID。
     * @param  string  $title          公告标题。
     * @param  string  $summary        公告摘要。
     * @param  string  $body           公告内容。
     * @param  int     $terminal       所属终端。
     * @param  int     $isDialog       是否弹框。
     * @param  string  $dialogEndTime  弹框截止时间。
     *
     * @return void
     */
    public static function add($adminId, $title, $summary, $body, $terminal, $isDialog, $dialogEndTime)
    {
        $rules = [
            'title'           => '公告标题|require|len:1:64:1',
            'summary'         => '公告摘要|require|len:1:255:1',
            'body'            => '公告内容|require|len:1:50000:1',
            'terminal'        => '所属终端|require|len:1:10:0',
            'is_dialog'       => '是否弹框|require|integer|number_between:0:1',
            'dialog_end_time' => '弹框截止时间|date'
        ];
        $data = [
            'title'           => $title,
            'summary'         => $summary,
            'body'            => $body,
            'terminal'        => $terminal,
            'is_dialog'       => $isDialog,
            'dialog_end_time' => $dialogEndTime
        ];
        Validator::valido($data, $rules);
        self::checkTerminal($terminal);
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
            Core::exception(STATUS_SERVER_ERROR, '');
        }
    }

    /**
     * 添加公告。
     *
     * @param  int     $adminId        管理员 ID。
     * @param  int     $noticeid       公告 ID。
     * @param  string  $title          公告标题。
     * @param  string  $summary        公告摘要。
     * @param  string  $body           公告内容。
     * @param  int     $terminal       所属终端。
     * @param  int     $isDialog       是否弹框。
     * @param  string  $dialogEndTime  弹框截止时间。
     *
     * @return void
     */
    public static function edit($adminId, $noticeid, $title, $summary, $body, $terminal, $isDialog, $dialogEndTime)
    {
        $rules = [
            'title'           => '公告标题|require|len:1:64:1',
            'summary'         => '公告摘要|require|len:1:255:1',
            'body'            => '公告内容|require|len:1:50000:1',
            'terminal'        => '所属终端|require|len:1:10:0',
            'is_dialog'       => '是否弹框|require|integer|number_between:0:1',
            'dialog_end_time' => '弹框截止时间|date'
        ];
        $data = [
            'title'           => $title,
            'summary'         => $summary,
            'body'            => $body,
            'terminal'        => $terminal,
            'is_dialog'       => $isDialog,
            'dialog_end_time' => $dialogEndTime
        ];
        Validator::valido($data, $rules);
        self::checkTerminal($terminal);
        $NoticeModel = new NoticeModel();
        $notice = $NoticeModel->fetchOne([], ['noticeid' => $noticeid]);
        if (empty($notice) || $notice['cur_status'] == NoticeModel::STATUS_DELETED) {
            Core::exception(STATUS_SERVER_ERROR, '公告不存在或已经删除');
        }
        $data['terminal']   = self::sumTerminal($terminal);
        $data['cur_status'] = NoticeModel::STATUS_YES;
        $data['u_time']     = date('Y-m-d H:i:s', time());
        $data['u_by']       = $adminId;
        $status = $NoticeModel->update($data, ['noticeid' => $noticeid]);
        if (!$status) {
            Core::exception(STATUS_SERVER_ERROR, '更新失败');
        }
    }

    /**
     * 检查表单提交的终端值。
     *
     * @param  string  $terminal  终端值。格式：1,2,4。
     *
     * @return void
     */
    protected static function checkTerminal($terminal)
    {
        if (strlen($terminal) == 0) {
            Core::exception(STATUS_SERVER_ERROR, '终端值未设置');
        }
        $arrTerminal = explode(',', $terminal);
        foreach ($arrTerminal as $t) {
            if (!is_numeric($t)) {
                Core::exception(STATUS_SERVER_ERROR, '终端值格式不正确');
            }
            if (!array_key_exists($t, self::$terminalDict)) {
                Core::exception(STATUS_SERVER_ERROR, '所属终端值有误');
            }
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
        $columns     = ['noticeid', 'title', 'summary', 'body', 'terminal', 'is_dialog', 'dialog_end_time'];
        $detail      = $NoticeModel->fetchOne($columns, [
            'noticeid'   => $noticeid,
            'cur_status' => ['!=', NoticeModel::STATUS_DELETED]]
        );
        if (empty($detail)) {
            Core::exception(STATUS_SERVER_ERROR, '公告不存在或已经删除');
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
            Core::exception(STATUS_SERVER_ERROR, '公告不存在或已经删除');
        }
        $data['cur_status'] = $status;
        $data['u_time']     = date('Y-m-d H:i:s', time());
        $data['u_by']       = $adminId;
        $status = $NoticeModel->update($data, ['noticeid' => $noticeid]);
        if (!$status) {
            Core::exception(STATUS_SERVER_ERROR, '更新失败');
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
        if (($bitVal & self::TERMINAL_IOS) == self::TERMINAL_IOS) {
            $data[] = self::TERMINAL_IOS;
        }
        if (($bitVal & self::TERMINAL_ANDROID) == self::TERMINAL_ANDROID) {
            $data[] = self::TERMINAL_ANDROID;
        }
        if (($bitVal & self::TERMINAL_H5) == self::TERMINAL_H5) {
            $data[] = self::TERMINAL_H5;
        }
        if (($bitVal & self::TERMINAL_PC) == self::TERMINAL_PC) {
            $data[] = self::TERMINAL_PC;
        }
        if (($bitVal & self::TERMINAL_MINI_PROGRAM) == self::TERMINAL_MINI_PROGRAM) {
            $data[] = self::TERMINAL_MINI_PROGRAM;
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
        if (($bitVal & self::TERMINAL_IOS) == self::TERMINAL_IOS) {
            $data[] = self::$terminalDict[self::TERMINAL_IOS];
        }
        if (($bitVal & self::TERMINAL_ANDROID) == self::TERMINAL_ANDROID) {
            $data[] = self::$terminalDict[self::TERMINAL_ANDROID];
        }
        if (($bitVal & self::TERMINAL_H5) == self::TERMINAL_H5) {
            $data[] = self::$terminalDict[self::TERMINAL_H5];
        }
        if (($bitVal & self::TERMINAL_PC) == self::TERMINAL_PC) {
            $data[] = self::$terminalDict[self::TERMINAL_PC];
        }
        if (($bitVal & self::TERMINAL_MINI_PROGRAM) == self::TERMINAL_MINI_PROGRAM) {
            $data[] = self::$terminalDict[self::TERMINAL_MINI_PROGRAM];
        }
        return $data ? implode(',', $data) : '-';
    }
}