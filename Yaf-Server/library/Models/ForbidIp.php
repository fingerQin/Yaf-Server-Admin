<?php
/**
 * 系统 IP 封禁表。
 * @author fingerQin
 * @date 2019-06-05
 */

namespace Models;

class ForbidIp extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName  = 'finger_forbid_ip';

    protected $primaryKey = 'id';
}