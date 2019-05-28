<?php
/**
 * 用户活跃度日志表 Model。
 * @author fingerQin
 * @date 2019-05-28
 */

namespace Models;

class ActiveLog extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_active_log';

    protected $primaryKey = 'id';
}