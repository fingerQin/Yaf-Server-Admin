<?php
/**
 * 短信模板表 Model。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Models;

class SmsTpl extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName = 'finger_sms_tpl';

    protected $primaryKey = 'id';

    /**
     * 触发类型。
     */
    const TRIGGER_TYPE_USER   = 1; // 用户触发。
    const TRIGGER_TYPE_SYSTEM = 2; // 系统触发。
}