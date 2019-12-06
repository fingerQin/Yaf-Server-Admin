<?php
/**
 * 系统消息。
 * @author fingerQin
 * @date 2019-04-18
 */

namespace Services\System;

use finger\Core;
use finger\Database\Db;
use finger\DataInput;
use finger\Date;
use finger\Validator;
use Models\User;
use Models\Message as MessageModel;

class Message extends \Services\AbstractBase
{
    /**
     * 消息列表。
     *
     * @param  string  $mobile      手机账号。
     * @param  int     $readStatus  阅读状态。
     * @param  int     $page        页码。
     * @param  int     $count       每页条数。
     *
     * @return array
     */
    public static function lists($mobile = '', $readStatus = -1, $page = 1, $count = 20)
    {
        $from       = ' FROM finger_message AS a INNER JOIN finger_user AS b ON(a.userid=b.userid)';
        $offset     = self::getPaginationOffset($page, $count);
        $columns    = ' a.msgid,a.msg_type,a.is_read,a.userid,a.title,a.u_time,a.url,a.content,a.c_time,b.mobile,b.nickname ';
        $whereCount = ' WHERE status = 1 ';
        $whereList  = ' WHERE a.status = 1 ';
        $params     = [];
        if (strlen($mobile) > 0) {
            $UserModel = new User();
            $userinfo  = $UserModel->fetchOne([], ['mobile' => $mobile]);
            $userid    = $userinfo ? $userinfo['userid'] : 0;
            $whereCount .= ' AND userid = :userid ';
            $whereList  .= ' AND a.userid = :userid ';
            $params[':userid'] = $userid;
        }
        if ($readStatus != -1) {
            $whereCount .= ' AND is_read = :is_read ';
            $whereList  .= ' AND a.is_read = :is_read ';
            $params[':is_read'] = $readStatus;
        }
        $orderBy = ' ORDER BY a.msgid DESC ';
        $sql     = "SELECT COUNT(1) AS count FROM finger_message {$whereCount}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$from} {$whereList} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        foreach ($list as $k => $val) {
            $val['u_time']         = Date::formatDateTime($val['u_time']);
            $val['read_label']     = MessageModel::$readDict[$val['is_read']];
            $val['msg_type_label'] = DataInput::getString(MessageModel::$msgTypeDict, $val['msg_type'], '-');
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
     * 添加系统消息。
     *
     * @param  int     $msgType  消息类型。
     * @param  string  $mobile   手机账号。
     * @param  string  $title    消息标题。
     * @param  string  $content  消息内容。
     * @param  string  $url      跳转地址。
     *
     * @return void
     */
    public static function add($msgType, $mobile, $title, $content, $url)
    {
        $rules = [
            'msg_type' => '消息类型|require|number_between:1:2',
            'mobile'   => '手机账号|require|mobilephone',
            'title'    => '消息标题|require|len:1:64:1',
            'content'  => '消息内容|require|len:1:255:1',
            'url'      => '跳转URL|url'
        ];
        $data = [
            'msg_type' => $msgType,
            'mobile'   => $mobile,
            'title'    => $title,
            'content'  => $content,
            'url'      => $url
        ];
        Validator::valido($data, $rules);
        $UserModel = new User();
        $userinfo  = $UserModel->fetchOne([], ['mobile' => $mobile]);
        if (empty($userinfo)) {
            Core::exception(STATUS_SERVER_ERROR, '手机账号不存在');
        }
        unset($data['mobile']);
        $datetime = date('Y-m-d H:i:s', time());
        $data['c_time'] = $datetime;
        $data['u_time'] = $datetime;
        $data['userid'] = $userinfo['userid'];
        $MessageModel   = new MessageModel();
        $status = $MessageModel->insert($data);
        if (!$status) {
            Core::exception(STATUS_SERVER_ERROR, '发送失败');
        }
    }

    /**
     * 删除。
     *
     * @param  int  $adminId  管理员 ID。
     * @param  int  $msgid    系统消息 ID。
     *
     * @return void
     */
    public static function delete($adminId, $msgid)
    {
        $MessageModel = new MessageModel();
        $message = $MessageModel->fetchOne([], ['msgid' => $msgid]);
        if (empty($message)) {
            Core::exception(STATUS_SERVER_ERROR, '消息不存在');
        }
        if ($message['status'] == MessageModel::STATUS_DELETED) {
            Core::exception(STATUS_SERVER_ERROR, '已经删除');
        }
        $updata = ['status' => MessageModel::STATUS_DELETED, 'u_time' => date('Y-m-d H:i:s', time())];
        $where  = ['msgid' => $msgid];
        $status = $MessageModel->update($updata, $where);
        if (!$status) {
            Core::exception(STATUS_SERVER_ERROR, '删除失败');
        }
    }
}