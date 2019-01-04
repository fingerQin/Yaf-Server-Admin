<?php
/**
 * 管理员角色表 Model。
 * @author fingerQin
 * @date 2018-07-06
 */

namespace Models;

class AdminRole extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName = 'finger_admin_role';

    protected $primaryKey = 'roleid';
}