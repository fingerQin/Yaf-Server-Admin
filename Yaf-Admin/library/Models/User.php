<?php
/**
 * 用户表模型。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Models;

class User extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'finger_user';

    protected $primaryKey = 'userid';

    /**
     * 用户状态常量。
     */
    const STATUS_INVALID = 0; // 无效。
    const STATUS_NORMAL  = 1; // 正常。
    const STATUS_LOCKED  = 2; // 锁定/禁用。
    const STATUS_FREEZE  = 3; // 冻结。

    /**
     * 用户状态中文标签含义。
     *
     * @var array
     */
    public static $statusLabel = [
        self::STATUS_INVALID => '无效',
        self::STATUS_NORMAL  => '正常',
        self::STATUS_LOCKED  => '禁用',
        self::STATUS_FREEZE  => '冻结'
    ];
}