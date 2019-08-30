<?php
/**
 * 短信配置表 Model。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Models;

class SmsConf extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_sms_conf';

    protected $primaryKey = 'id';
}