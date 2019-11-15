<?php
/**
 * 配置表 Model。
 * 
 * @author fingerQin
 * @date 2018-07-08
 */

namespace Models;

class Config extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_config';

    protected $primaryKey = 'configid';
}