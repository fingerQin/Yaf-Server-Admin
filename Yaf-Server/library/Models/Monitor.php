<?php
/**
 * 监控表 Model。
 * 
 * @author fingerQin
 * @date 2019-04-18
 */

namespace Models;

class Monitor extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_monitor';

    protected $primaryKey = 'id';

    /**
     * 处理状态。
     */
    const STATUS_UNTREATED = 0; // 未处理。
    const STATUS_PROCESSED = 1; // 已处理。

    /**
     * 状态字典。
     *
     * @var array
     */
    public static $statusDict = [
        self::STATUS_UNTREATED => '未处理',
        self::STATUS_PROCESSED => '已处理'
    ];

    /**
     * CODE 编码。
     */
    const CODE_UNAUTHORIZED  = 'unauthorized'; // 未经授权访问其他应用接口。

    /**
     * 埋点位置编码字典。
     *
     * @var array
     */
    public static $codeDict = [
        self::CODE_UNAUTHORIZED
    ];
}