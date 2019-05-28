<?php
/**
 * 注册日志表 Model。
 * @author fingerQin
 * @date 2019-05-28
 */

namespace Models;

class RegisterLog extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_register_log';

    protected $primaryKey = 'id';
}