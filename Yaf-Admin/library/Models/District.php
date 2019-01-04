<?php
/**
 * 地区信息表。
 * @author fingerQin
 * @date 2018-08-09
 */

namespace Models;

class District extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'finger_district';

    /**
     * 区域类型。
     */
    const REGION_TYPE_PROVINCE = 1; // 省。
    const REGION_TYPE_CITY     = 2; // 市。
    const REGION_TYPE_COUNTY   = 3; // 区县。
    const REGION_TYPE_STREET   = 4; // 街道。
}