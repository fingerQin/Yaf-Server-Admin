<?php
/**
 * 公告表 Model。
 * 
 * @author fingerQin
 * @date 2019-04-18
 */

namespace Models;

class Notice extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_notice';

    protected $primaryKey = 'noticeid';

    /**
     * 终端常量。
     * --主要解决公告多端显示问题。
     */
    const TERMINAL_APP = 1;
    const TERMINAL_M   = 2;
    const TERMINAL_PC  = 4;

    /**
     * 终端常量字典。
     */
    public static $terminalDict = [
        self::TERMINAL_APP => 'APP',
        self::TERMINAL_M   => 'M站',
        self::TERMINAL_PC  => 'PC'
    ];

    /**
     * 状态字典。
     *
     * @var array
     */
    public static $statusDict = [
        self::STATUS_INVALID => '隐藏',
        self::STATUS_DELETED => '删除',
        self::STATUS_YES     => '显示'
    ];
}