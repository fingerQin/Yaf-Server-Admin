<?php
/**
 * 用户金币表 Model。
 * @author fingerQin
 * @date 2019-08-07
 */

namespace Models;

class Gold extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_gold';

    protected $primaryKey = 'id';
}