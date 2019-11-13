<?php
/**
 * 用户地址表模型。
 * @author fingerQin
 * @date 2019-04-09
 */

namespace Models;

class UserAddress extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName  = 'finger_user_address';

    protected $primaryKey = 'addressid';

    /**
     * 默认地址。
     */
    const DEFAULT_YES = 1; // 是。
    const DEFAULT_NO  = 0; // 否。
}