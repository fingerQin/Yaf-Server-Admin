<?php
/**
 * 登录日志表 Model。
 * @author fingerQin
 * @date 2019-05-28
 */

namespace Models;

class LoginLog extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_login_log';

    protected $primaryKey = 'id';
}