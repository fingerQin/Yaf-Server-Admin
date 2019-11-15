<?php
/**
 * 告警表 Model。
 * 
 * @author fingerQin
 * @date 2019-06-18
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
    const STATUS_ING       = 0; // 未处理。
    const STATUS_PROCESSED = 1; // 已处理。

    /**
     * 处理状态字典。
     *
     * @var array
     */
    public static $statusDict = [
        self::STATUS_ING        => '未处理',
        self::STATUS_PROCESSED  => '已处理'
    ];

    /**
     * 告警编码。
     */
    const CODE_UNAUTHORIZED  = 'unauthorized'; // 未经授权访问其他应用接口。

    /**
     * 告警编码字典。
     *
     * @var array
     */
    public static $codeDict = [
        self::CODE_UNAUTHORIZED => '未授权 APPID 访问接口'
    ];
}