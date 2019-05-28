<?php
/**
 * 统计:登录汇总表 Model。
 * @author fingerQin
 * @date 2019-05-28
 */

namespace Models;

class StatsLogin extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_stats_login';

    protected $primaryKey = 'id';
}