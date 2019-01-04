<?php
/**
 * Model 基类表。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Models;

abstract class AbstractBase extends \finger\Database\Models
{
    /**
     * 表更新时间。
     * 
     * @var string
     */
    protected $createTime = 'c_time';

    /**
     * 更新时间字段。
     * 
     * @var string
     */
    protected $updateTime = 'u_time';

    /**
     * 状态。
     */
    const STATUS_NO      = 0; // No
    const STATUS_INVALID = 0; // 无效。
    const STATUS_YES     = 1; // 正常。
    const STATUS_DELETED = 2; // 已删除。
}