<?php
/**
 * 统计:短信发送汇总表 Model。
 * @author fingerQin
 * @date 2019-05-28
 */

namespace Models;

class StatsSms extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_stats_sms';

    protected $primaryKey = 'id';
}