<?php
/**
 * API 应用权限表 Model。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Models;

class ApiAuth extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_api_auth';

    protected $primaryKey = 'id';
}