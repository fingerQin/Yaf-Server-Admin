<?php
/**
 * 事件表 Model。
 * @author fingerQin
 * @date 2018-09-06
 */

namespace Models;

class Event extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName  = 'finger_event';

    protected $primaryKey = 'id';

    /**
     * 更新时间字段。
     * 
     * @var string
     */
    protected $updateTime = false;

    /**
     * 事件处理状态。
     */
    const STATUS_INIT    = 0; // 待处理。
    const STATUS_SUCCESS = 1; // 处理成功。
    const STATUS_FAIL    = 2; // 处理失败。

    /**
     * 事件类型。
     */
    const CODE_REGISTER = 'register'; // 注册事件。
    const CODE_LOGIN    = 'login';    // 登录事件。通常可以使用此类型的事件做异常登录风险检测。

    /**
     * 事件类型 CODE 字典。
     *
     * @var array
     */
    public static $codeDict = [
        self::CODE_REGISTER,
        self::CODE_LOGIN
    ];
}