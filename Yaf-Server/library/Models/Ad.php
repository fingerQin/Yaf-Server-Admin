<?php
/**
 * 广告表 Model。
 * @author fingerQin
 * @date 2018-08-20
 */

namespace Models;

class Ad extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_ad';

    protected $primaryKey = 'ad_id';
}