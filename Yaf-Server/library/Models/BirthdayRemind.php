<?php
/**
 * 生日提醒表。
 * @author fingerQin
 * @date 2019-07-26
 */

namespace Models;

class BirthdayRemind extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName  = 'finger_birthday_remind';

    protected $primaryKey = 'id';
}