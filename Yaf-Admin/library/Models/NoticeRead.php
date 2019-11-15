<?php
/**
 * 公告阅读标记表 Model。
 * 
 * @author fingerQin
 * @date 2019-04-18
 */

namespace Models;

class NoticeRead extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_notice_read';

    protected $primaryKey = 'id';
}