<?php
/**
 * APP客户端升级表 Model。
 * 
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Models;

class SmsBlacklist extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName = 'finger_sms_blacklist';

    protected $primaryKey = 'id';
}