<?php
/**
 * 系统公告。
 * @author fingerQin
 * @date 2019-04-19
 */

namespace Services\System;

use Utils\YCore;
use finger\Database\Db;
use Models\Notice as NoticeModel;
use Models\NoticeRead;

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
        $columns = ' noticeid,title,summary,c_time ';
        $where   = ' WHERE cur_status = :cur_status ';
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

    /**
     * 公告详情。
     * 
     * -- 每次调用详情则更新一次最后阅读时间。
     *
     * @param  int  $userid    用户 ID。
     * @param  int  $noticeid  公告 ID。
     * @param  int  $platform  平台。
     *
     * @return array
     */
    public static function detail($userid = 0, $noticeid, $platform)
    {
        $columns = 'noticeid,title,summary,body,c_time';
        $sql = "SELECT {$columns} FROM tb_notice WHERE "
             . "noticeid = :noticeid AND cur_status = :cur_status "
             . "AND terminal & :terminal_left = :terminal_right LIMIT 1";
        $terminal = self::terminalBitVal($platform);
        $params   = [
            ':noticeid'       => $noticeid,
            'cur_status'      => NoticeModel::STATUS_YES,
            ':terminal_left'  => $terminal,
            ':terminal_right' => $terminal
        ];
        $detail = Db::one($sql, $params);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '公告不存在或已经删除');
        }
        self::markRead($userid);
        return $detail;
    }

    /**
     * 获取 app 弹窗公告。
     *
     * @return array
     */
    public static function appDialog()
    {
        $datetime = date('Y-m-d H:i:s');
        $where = [
            'is_dialog'       => NoticeModel::STATUS_YES,
            'dialog_end_time' => ['>=', $datetime]
        ];
        $columns     = ['noticeid', 'title', 'summary', 'u_time'];
        $NoticeModel = new NoticeModel();
        $data        = $NoticeModel->fetchOne($columns, $where, 'noticeid DESC');
        if (empty($data)) {
            return YCore::getNullObject();
        } else {
            $data['edition'] = str_pad($data['noticeid'], 10, '0', STR_PAD_RIGHT) . strtotime($data['u_time']);
            unset($data['noticeid'], $data['u_time']);
            return $data;
        }
    }

    /**
     * 未读条数。
     * 
     * -- 未登录则为 0。以最后阅读时间到当前时间是否有新发布的公告。
     * -- 刚注册或未曾看过公告，则获取最近 3 个月发布的公告数。
     *
     * @param  int  $userid  用户 ID。
     *
     * @return int
     */
    public static function unreadCount($userid)
    {
        if ($userid == 0) {
            return 0;
        }
        $NoticeReadModel = new NoticeRead();
        $lastReadData    = $NoticeReadModel->fetchOne(['last_read_time'], ['userid' => $userid]);
        $lastReadTime    = $lastReadData['last_read_time'] ?? date('Y-m-d 00:00:00', strtotime('-90day'));
        $where = [
            'c_time' => ['>', $lastReadTime]
        ];
        $NoticeModel = new NoticeModel();
        return $NoticeModel->count($where);
    }

    /**
     * 已读标记。
     * 
     * -- 通过最后阅读的时间来获取未读公告数量。
     *
     * @param  int  $userid  用户 ID。
     *
     * @return void
     */
    private static function markRead($userid)
    {
        if ($userid == 0) {
            return;
        }
        $NoticeRead  = new NoticeRead();
        $notice      = $NoticeRead->fetchOne([], ['userid' => $userid], '', '', $isMaster = true);
        $datetime    = date('Y-m-d H:i:s');
        if (empty($notice)) {
            $data = [
                'userid'         => $userid,
                'last_read_time' => $datetime,
                'u_time'         => $datetime,
                'c_time'         => $datetime
            ];
            $NoticeRead->insert($data);
        } else {
            $updata = [
                'last_read_time' => $datetime,
                'u_time'         => $datetime
            ];
            $NoticeRead->update($updata, ['id' => $notice['id']]);
        }
    }
}