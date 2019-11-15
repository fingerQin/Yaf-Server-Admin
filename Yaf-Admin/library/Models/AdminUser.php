<?php
/**
 * 管理员表 Model。
 * @author fingerQin
 * @date 2018-07-06
 */

namespace Models;

class AdminUser extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_admin_user';

    protected $primaryKey = 'adminid';
}