<?php
/**
 * 统计:用户活跃度汇总表 Model。
 * @author fingerQin
 * @date 2019-05-28
 */

namespace Models;

class StatsActive extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_stats_active';

    protected $primaryKey = 'id';
}