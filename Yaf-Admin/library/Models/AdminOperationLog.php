<?php
/**
 * 管理员操作日志表 Model。
 * @author fingerQin
 * @date 2019-04-23
 */

namespace Models;

class AdminOperationLog extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_admin_operation_log';

    protected $primaryKey = 'id';

    protected $updateTime = false;
}