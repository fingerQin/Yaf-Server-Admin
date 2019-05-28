<?php
/**
 * 广告位置接表 Model。
 * @author fingerQin
 * @date 2018-08-20
 */

namespace Models;

class AdPosition extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_ad_position';

    protected $primaryKey = 'pos_id';
}