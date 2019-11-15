<?php
/**
 * 管理后台操作日志。
 * @author fingerQin
 * @date 2019-04-23
 */

namespace Services\System;

use Models\AdminOperationLog;

class OperationLog extends \Services\AbstractBase
{
    /**
     * 日志添加。
     *
     * @param  int           $adminId   管理员 ID。
     * @param  string        $realname  管理员姓名。
     * @param  string        $ip        IP 访问地址。
     * @param  string        $c         Controller 名称。
     * @param  string        $a         Action 名称。
     * @param  string|array  $content   日志内容。
     *
     * @return void
     */
    public static function add($adminId, $realname, $ip, $c, $a, $content)
    {
        $data = [
            'adminid'  => $adminId,
            'realname' => $realname,
            'ip'       => $ip,
            'c'        => $c,
            'a'        => $a,
            'content'  => is_array($content) ? print_r($content, true) : $content,
            'c_time'   => date('Y-m-d H:i:s')
        ];
        $AdminOperationLogModel = new AdminOperationLog();
        $AdminOperationLogModel->insert($data);
    }
}