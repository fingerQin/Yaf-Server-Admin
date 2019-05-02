<?php
/**
 * 系统配置表 Model。
 * @author fingerQin
 * @date 2019-09-02
 */

namespace Models;

class Config extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName = 'finger_config';

    protected $primaryKey = 'configid';
}