<?php
/**
 * 友情链接表 Model。
 * @author fingerQin
 * @date 2018-09-05
 */

namespace Models;

class Link extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName  = 'finger_link';

    protected $primaryKey = 'link_id';
}