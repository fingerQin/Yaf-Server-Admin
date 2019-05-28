<?php
/**
 * 短信发送统计。
 * @author fingerQin
 * @date 2019-05-28
 */

namespace Services\Stats\Sms;

use Models\SmsTpl;
use Models\SmsSendLog;
use finger\Database\Db;

class Stats extends \Services\AbstractBase
{
    /**
     * 统计短信发送数量。
     *
     * -- 当不传指定日期就以当前日期进行统计。
     * 
     * @param  string  $datetime  指定日期。格式：2019-05-28 12:00:00
     *
     * @return void
     */
    public static function send($datetime = '')
    {
        $datetime  = (strlen($datetime) > 0) ? $datetime : date('Y-m-d H:i:s', TIMESTAMP);
        $timestamp = strtotime($datetime);
        $startTime = date('Y-m-d H:00:00', $timestamp - 3600);
        $endTime   = date('Y-m-d H:59:59', $timestamp - 3600);
        $week      = date('N', $timestamp);

        $sql = 'SELECT COUNT(1) AS count,tpl_id,sms_status FROM '
             . 'finger_sms_sendlog WHERE c_time BETWEEN :startTime '
             . 'AND :endTime GROUP BY tpl_id, sms_status';
        $params = [
            ':startTime' => $startTime,
            ':endTime'   => $endTime
        ];
        $result = Db::query($sql, $params);
        $data   = [];
        foreach ($result as $item) {
            if ($item['sms_status'] == SmsSendLog::SEND_STATUS_SENT) {
                $data[$item['tpl_id']]['s_count'] = $item['count'];
            } elseif ($item['sms_status'] == SmsSendLog::SEND_STATUS_FAILD) {
                $data[$item['tpl_id']]['s_count_failed'] = $item['count'];
            } if ($item['sms_status'] == SmsSendLog::SEND_STATUS_CREATE) {
                $data[$item['tpl_id']]['s_count_ing'] = $item['count'];
            }
        }
        // 入库。
        self::resultWriteDb($data, $startTime, $week);
    }

    /**
     * 统计结果入库。
     *
     * @param  array   $result     统计结果。
     * @param  string  $startTime  统计时段。
     * @param  int     $week       
     * 
     * @return void
     */
    protected static function resultWriteDb($result, $startTime, $week)
    {
        if (!empty($result)) {
            $tplsDict = self::getAllSmsTpl();
            Db::beginTransaction();
            foreach ($result as $tplId => $item) {
                if (isset($tplsDict[$tplId])) {
                    $sql = 'REPLACE INTO finger_stats_sms (s_date,s_week,s_code,s_count,s_count_failed,s_count_ing) '
                         . 'VALUES(:s_date,:s_week,:s_code,:s_count,:s_count_failed,:s_count_ing)';
                    $params = [
                        ':s_date'         => $startTime,
                        ':s_week'         => $week,
                        ':s_code'         => $tplsDict[$tplId],
                        ':s_count'        => isset($item['s_count']) ? $item['s_count'] : 0,
                        ':s_count_failed' => isset($item['s_count_failed']) ? $item['s_count_failed'] : 0,
                        ':s_count_ing'    => isset($item['s_count_ing']) ? $item['s_count_ing'] : 0
                    ];
                    $status = Db::execute($sql, $params);
                    if (!$status) {
                        Db::rollBack();
                    }
                } else {
                    continue;
                }
            }
            Db::commit();
        }
    }

    /**
     * 获取全部短信模板 id 与 id 映射的 key 码。
     *
     * @return array
     */
    protected static function getAllSmsTpl()
    {
        $SmsTplModel = new SmsTpl();
        $result      = $SmsTplModel->fetchAll();
        $tplResult   = [];
        foreach ($result as $item) {
            $tplResult[$item['id']] = $item['send_key'];
        }
        return $tplResult;
    }
}