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