<?php
/**
 * 统计:注册汇总表 Model。
 * @author fingerQin
 * @date 2019-05-28
 */

namespace Models;

class StatsRegister extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_stats_register';

    protected $primaryKey = 'id';
}