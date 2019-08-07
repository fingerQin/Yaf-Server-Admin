<?php
/**
 * 订单主表。
 * @author fingerQin
 * @date 2019-08-07
 */

namespace Models;

class MallOrder extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'mall_order';

    const ORDER_STATUS_PAY_OK   = 1;    // 已付款。
    const ORDER_STATUS_DELIVER  = 2;    // 已发货。
    const ORDER_STATUS_SUCCESS  = 3;    // 交易成功。
    const ORDER_STATUS_CLOSED   = 4;    // 系统关闭。

    /**
     * 订单状态字典。
     *
     * @var array
     */
    public static $orderStatusDict = [
        self::ORDER_STATUS_PAY_OK   => '已付款',
        self::ORDER_STATUS_DELIVER  => '已发货',
        self::ORDER_STATUS_SUCCESS  => '交易成功',
        self::ORDER_STATUS_CLOSED   => '系统关闭'
    ];
}