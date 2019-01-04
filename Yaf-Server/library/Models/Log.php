<?php
/**
 * 日志表 Model。
 * @author fingerQin
 * @date 2018-09-20
 */

namespace Models;

class Log extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'finger_log';

    protected $primaryKey = 'logid';

    /**
     * 更新时间字段。
     * 
     * @var string
     */
    protected $updateTime = false;
}