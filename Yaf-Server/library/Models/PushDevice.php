<?php
/**
 * 用户设备唯一标识 Model。
 * @author fingerQin
 * @date 2018-06-29
 */

namespace Models;

class PushDevice extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName = 'finger_push_device';

    protected $primaryKey = 'id';
}