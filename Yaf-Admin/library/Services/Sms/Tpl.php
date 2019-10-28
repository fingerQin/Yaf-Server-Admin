<?php
/**
 * 短信模板管理。
 * @author fingerQin
 * @date 2019-04-17
 */

namespace Services\Sms;

use Models\SmsTpl;
use finger\Utils\YDate;
use finger\Utils\YCore;
use finger\Database\Db;
use finger\Validator;

class Tpl extends \Services\AbstractBase
{
    /**
     * 获取全部短信模板。
     *
     * @return array
     */
    public static function all()
    {
        $SmsTplModel = new SmsTpl();
        $columns = 'id,title';
        $where   = [];
        return $SmsTplModel->fetchAll($columns, $where);
    }

    /**
     * 以字典方式返回短信模板。
     *
     * @return array
     */
    public static function dict()
    {
        $tpls = self::all();
        $data = [];
        foreach ($tpls as $tpl) {
            $data[$tpl['id']] = $tpl['title'];
        }
        return $data;
    }

    /**
     * 模板详情。
     *
     * @param  int  $id  记录 ID。
     *
     * @return array
     */
    public static function detail($id)
    {
        $SmsTplModel = new SmsTpl();
        $columns = 'id,title,send_key,sms_body,trigger_type';
        $where   = ['id' => $id];
        $data    = $SmsTplModel->fetchOne($columns, $where);
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '该模板不存在或已经删除');
        }
        return $data;
    }

    /**
     * 模板列表。
     * 
     * @param  string  $sendKey  模板 KEY。
     * @param  int     $page     页码。
     * @param  int     $count    每页显示条数。
     * 
     * @return array
     */
    public static function lists($sendKey = '', $page = 1, $count = 20)
    {
        $from    = ' FROM finger_sms_tpl ';
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' id,send_key,title,sms_body,trigger_type,u_time,c_time ';
        $where   = ' WHERE 1 ';
        $params  = [];
        if (strlen($sendKey) > 0) {
            $where .= ' AND send_key = :send_key ';
            $params[':send_key'] = $sendKey;
        }
        $orderBy   = ' ORDER BY id DESC ';
        $sql       = "SELECT COUNT(1) AS count {$from} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$from} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        foreach ($list as $k => $val) {
            $val['u_time']             = YDate::formatDateTime($val['u_time']);
            $val['trigger_type_label'] = SmsTpl::$triggerTypeDict[$val['trigger_type']];
            $list[$k]                  = $val;
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
     * 模板添加。
     *
     * @param  int     $adminId      管理员 ID。
     * @param  string  $sendKey      模板发送 KEY。
     * @param  string  $title        模板标题。
     * @param  string  $smsBody      模板内容。
     * @param  int     $triggerType  触发类型。
     *
     * @return void
     */
    public static function add($adminId, $sendKey, $title, $smsBody, $triggerType)
    {
        $rules = [
            'send_key' => '模板KEY|require|alpha_dash|len:1:30:0',
            'title'    => '模板标题|require|len:1:30:1',
            'sms_body' => '模板内容|require|len:1:100:1'
        ];
        $data = [
            'send_key' => $sendKey,
            'title'    => $title,
            'sms_body' => $smsBody
        ];
        Validator::valido($data, $rules);
        if (self::isExistSendKey($sendKey)) {
            YCore::exception(STATUS_SERVER_ERROR, '模板 KEY 已经存在，请更换');
        }
        $datetime = date('Y-m-d H:i:s', time());
        $data['op_id']        = $adminId;
        $data['c_time']       = $datetime;
        $data['u_time']       = $datetime;
        $data['trigger_type'] = $triggerType == SmsTpl::TRIGGER_TYPE_USER ?: SmsTpl::TRIGGER_TYPE_SYSTEM;
        $SmsTplModel = new SmsTpl();
        $status = $SmsTplModel->insert($data);
        if (!$status) {
            YCore::exception(STATUS_SERVER_ERROR, '添加失败');
        }
    }

    /**
     * 模板添加。
     *
     * @param  int     $adminId      管理员 ID。
     * @param  int     $id           模板 ID。
     * @param  string  $sendKey      模板发送 KEY。
     * @param  string  $title        模板标题。
     * @param  string  $smsBody      模板内容。
     * @param  int     $triggerType  触发类型。
     *
     * @return void
     */
    public static function edit($adminId, $id, $sendKey, $title, $smsBody, $triggerType)
    {
        $rules = [
            'send_key' => '模板KEY|require|alpha_dash|len:1:30:0',
            'title'    => '模板标题|require|len:1:30:1',
            'sms_body' => '模板内容|require|len:1:100:1'
        ];
        $data = [
            'send_key' => $sendKey,
            'title'    => $title,
            'sms_body' => $smsBody
        ];
        Validator::valido($data, $rules);

        $SmsTplModel = new SmsTpl();
        $detail = $SmsTplModel->fetchOne([], ['id' => $id]);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '模板不存在或已经删除!');
        }
        if ($detail['send_key'] != $sendKey && self::isExistSendKey($sendKey)) {
            YCore::exception(STATUS_SERVER_ERROR, '模板 KEY 已经存在，请更换');
        }
        $datetime = date('Y-m-d H:i:s', time());
        $data['op_id']        = $adminId;
        $data['u_time']       = $datetime;
        $data['trigger_type'] = $triggerType == SmsTpl::TRIGGER_TYPE_USER ?: SmsTpl::TRIGGER_TYPE_SYSTEM;
        $SmsTplModel = new SmsTpl();
        $status = $SmsTplModel->update($data, ['id' => $id]);
        if (!$status) {
            YCore::exception(STATUS_SERVER_ERROR, '更新失败');
        }
    }

    /**
     * 是否已存在短信模板 KEY。
     *
     * @param  string  $sendKey  模板 KEY。
     *
     * @return bool
     */
    private static function isExistSendKey($sendKey)
    {
        $SmsTplModel = new SmsTpl();
        $data = $SmsTplModel->fetchOne([], ['send_key' => $sendKey]);
        return $data ? true : false;
    }
}