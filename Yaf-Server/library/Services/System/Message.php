<?php
/**
 * 系统消息。
 * @author fingerQin
 * @date 2019-04-19
 */

namespace Services\System;

use Utils\YCore;
use finger\Database\Db;
use Models\Message as MessageModel;

class Message extends \Services\AbstractBase
{
    /**
     * 用户消息列表。
     *
     * @param  int  $userid  用户 ID。
     * @param  int  $page    页码。
     * @param  int  $count   每页显示条数。
     *
     * @return array
     */
    public static function lists($userid, $page = 1, $count = 20)
    {
        $from    = ' FROM finger_message ';
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' msgid,msg_type,type_ref_id,is_read,title,content,url,c_time ';
        $where   = ' WHERE userid = :userid AND status = :status ';
        $params  = [
            ':userid' => $userid,
            ':status' => MessageModel::STATUS_YES
        ];
        $orderBy = ' ORDER BY msgid DESC ';
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
     * 已读设置。
     *
     * @param  int  $userid  用户 ID。
     * @param  int  $msgid   消息 ID。
     *
     * @return void
     */
    public static function read($userid, $msgid)
    {
        $where = [
            'userid' => $userid,
            'msgid'  => $msgid,
            'status' => MessageModel::STATUS_YES
        ];
        $MessageModel = new MessageModel();
        $message = $MessageModel->fetchOne([], $where);
        if (empty($message)) {
            YCore::exception(STATUS_SERVER_ERROR, '该条消息不存在或已经删除');
        }
        $updata = [
            'is_read' => MessageModel::STATUS_YES,
            'u_time'  => date('Y-m-d H:i:s', time())
        ];
        $status = $MessageModel->update($updata, ['msgid' => $message['msgid']]);
        if (!$status) {
            YCore::exception(STATUS_SERVER_ERROR, '更新失败');
        }
    }
}