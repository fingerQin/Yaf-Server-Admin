<?php
/**
 * 短信发送日志管理。
 * @author fingerQin
 * @date 2019-04-17
 */

namespace Services\Sms;

use Utils\YCore;
use Utils\YDate;
use Models\SmsSendLog;
use finger\Validator;
use finger\Database\Db;
use Utils\YInput;

class Log extends \Services\AbstractBase
{
    /**
     * 日志列表。
     * 
     * @param  string  $mobile     手机号码。
     * @param  int     $status     状态码。
     * @param  int     $tplId      模板 ID。
     * @param  int     $channelId  通道标识。
     * @param  string  $startTime  开始时间。
     * @param  string  $endTime    截止时间。
     * @param  int     $page       页码。
     * @param  int     $count      每页显示条数。
     * 
     * @return array
     */
    public static function lists($mobile, $status = -1, $tplId = -1, $channelId = -1, $startTime = '', $endTime = '', $page = 1, $count = 20)
    {
        if (strlen($mobile) > 0 && !Validator::is_mobilephone($mobile)) {
            YCore::exception(STATUS_SERVER_ERROR, '手机号格式不正确');
        }
        if (strlen($startTime) > 0 && !Validator::is_date($startTime)) {
            YCore::exception(STATUS_SERVER_ERROR, '开始时间格式不对');
        }
        if (strlen($endTime) > 0 && !Validator::is_date($endTime)) {
            YCore::exception(STATUS_SERVER_ERROR, '截止时间格式不对');
        }
        $from    = ' FROM finger_sms_sendlog ';
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' * ';
        $where   = ' WHERE 1 ';
        $params  = [];
        if (strlen($mobile) > 0) {
            $where .= ' AND mobile = :mobile ';
            $params[':mobile'] = $mobile;
        }
        if (strlen($startTime) > 0) {
            $where .= ' AND c_time >= :starttime ';
            $params[':starttime'] = $startTime;
        }
        if (strlen($endTime) > 0) {
            $where .= ' AND c_time <= :endtime ';
            $params[':endtime'] = $endTime;
        }
        if ($status != -1) {
            $where .= ' AND sms_status = :sms_status ';
            $params[':sms_status'] = $status;
        }
        if ($tplId != -1) {
            $where .= ' AND tpl_id <= :tpl_id ';
            $params[':tpl_id'] = $tplId; 
        }
        if ($channelId != -1) {
            $where .= ' AND channel_id <= :channel_id ';
            $params[':channel_id'] = $channelId; 
        }
        $orderBy   = ' ORDER BY id DESC ';
        $sql       = "SELECT COUNT(1) AS count {$from} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$from} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        $tpls      = Tpl::dict();
        $channels  = Channel::dict();
        foreach ($list as $k => $val) {
            $val['s_time']       = YDate::formatDateTime($val['s_time']);
            $val['platform']     = self::$platformLabel[$val['platform']];
            $val['tpl_name']     = YInput::getString($tpls, $val['tpl_id'], '-');
            $val['channel_name'] = YInput::getString($channels, $val['channel_id'], '-');
            $val['sms_status']   = SmsSendLog::$statusDict[$val['sms_status']];
            $list[$k]            = $val;
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
}