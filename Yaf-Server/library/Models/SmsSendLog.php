<?php
/**
 * 短信发送日志表 Model。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Models;

class SmsSendLog extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_sms_sendlog';

    protected $primaryKey = 'id';

    /**
     * 更新时间字段。
     * 
     * @var string
     */
    protected $updateTime = false;

    /**
     * 创建时间字段。
     *
     * @var bool
     */
    protected $createTime = false;

    /**
     * 短信发送状态。
     */
    const SEND_STATUS_CREATE = 1; // 创建。
    const SEND_STATUS_SENT   = 2; // 已发送。
    const SEND_STATUS_FAILD  = 3; // 失败。

    /**
     * 验证码使用状态。
     */
    const STATUS_USED    = 1; // 已使用。
    const STATUS_UNUSED  = 2; // 未使用。
    const STATUS_INVALID = 3; // 已失效。

    /**
     * 短信类型。
     */
    const SMS_TYPE_TXT   = 1; // 文字短信。
    const SMS_TYPE_VOICE = 2; // 语音短信。
}