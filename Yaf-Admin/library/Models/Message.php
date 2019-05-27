<?php
/**
 * 系统消息 Model。
 * 
 * @author fingerQin
 * @date 2019-04-18
 */

namespace Models;

class Message extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName = 'finger_message';

    protected $primaryKey = 'msgid';

    /**
     * 消息类型。
     */
    const MSG_TYPE_SYSTEM  = 1; // 系统消息。
    const MSG_TYPE_WELFARE = 2; // 福利消息。

    /**
     * 消息类型字典。
     *
     * @var array
     */
    public static $msgTypeDict = [
        self::MSG_TYPE_SYSTEM  => '系统消息',
        self::MSG_TYPE_WELFARE => '福利消息'
    ];

    /**
     * 阅读状态。
     */
    const READ_NO  = 0; // 未读。
    const READ_YES = 1; // 已读。

    /**
     * 阅读状态字典。
     *
     * @var array
     */
    public static $readDict = [
        self::READ_YES => '已读',
        self::READ_NO  => '未读'
    ];
}