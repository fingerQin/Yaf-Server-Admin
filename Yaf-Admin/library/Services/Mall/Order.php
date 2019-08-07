<?php
/**
 * 订单业务封装。
 * @author fingerQin
 * @date 2019-08-07
 */

namespace Services\Mall;

use finger\Validator;
use finger\Database\Db;
use Utils\YCore;
use Utils\YDate;
use Models\District;
use Models\MallOrder;
use Models\MallOrderItem;
use Models\MallOrderLog;
use Models\MallLogistics;
use Services\AbstractBase;

class Order extends AbstractBase
{
    /**
     * 获取订单列表。
     *
     * @param  string  $goodsId         商品ID。
     * @param  string  $mobile          手机账号。
     * @param  string  $receiverName    收货人姓名。
     * @param  string  $receiverMobile  收货人手机。
     * @param  string  $orderSn         订单号。
     * @param  int     $orderStatus     订单状态。
     * @param  string  $startTime       成交时间开始。
     * @param  string  $endTime         成交时间结束。
     * @param  int     $page            当前页码。
     * @param  int     $count           每页显示条数。
     * @return array
     */
    public static function list($goodsId = -1, $mobile = '', $receiverName = '', $receiverMobile = '', $orderSn = '', 
    $orderStatus = -1, $startTime = '', $endTime = '', $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM mall_order AS a INNER JOIN finger_user AS b ON(a.userid=b.userid)';
        $columns   = ' * ';
        $where     = ' WHERE a.status = :status ';
        $params    = [
            ':status' => MallOrder::STATUS_YES
        ];
        if (strlen($mobile) > 0) {
            $where .= ' AND b.mobile = :mobile ';
            $params[':mobile'] = $mobile;
        }
        if (strlen($receiverMobile) > 0) {
            $where .= ' AND a.receiver_mobile = :receiver_mobile ';
            $params[':receiver_mobile'] = $receiverMobile;
        }
        if (strlen($receiverName) > 0) {
            $where .= ' AND a.receiver_name = :receiver_name ';
            $params[':receiver_name'] = $receiverName;
        }
        if (strlen($orderSn) > 0) {
            $where .= ' AND a.order_sn = :order_sn ';
            $params[':order_sn'] = $orderSn;
        }
        if ($orderStatus != -1) {
            $where .= ' AND a.order_status = :order_status ';
            $params[':order_status'] = $orderStatus;
        }
        if (strlen($startTime) > 0) {
            if (!Validator::is_date($startTime)) {
                YCore::exception(STATUS_SERVER_ERROR, '成交时间格式不正确');
            }
            $where .= ' AND a.c_time >= :start_time ';
            $params[':start_time'] = $startTime;
        }
        if (strlen($endTime) > 0) {
            if (!Validator::is_date($endTime)) {
                YCore::exception(STATUS_SERVER_ERROR, '成交时间格式不正确');
            }
            $where .= ' AND a.c_time <= :end_time ';
            $params[':end_time'] = $endTime;
        }
        $orderBy   = ' ORDER BY a.orderid DESC ';
        $sql       = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $item['goods_list']         = self::getOrderItems($item['orderid']);
            $item['u_time']             = YDate::formatDateTime($item['u_time']);
            $item['pay_time']           = YDate::formatDateTime($item['pay_time']);
            $item['shipping_time']      = YDate::formatDateTime($item['shipping_time']);
            $item['done_time']          = YDate::formatDateTime($item['done_time']);
            $item['closed_time']        = YDate::formatDateTime($item['closed_time']);
            $item['order_status_label'] = MallOrder::$orderStatusDict[$item['order_status']];
            $list[$key] = $item;
        }
        $result = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 获取订单购买的商品明细。
     *
     * @param  int  $orderId  订单ID。
     * @return array
     */
    protected static function getOrderItems($orderId)
    {
        $columns   = 'goodsid,goods_name,goods_image,productid,spec_val,market_price,sales_price,'
                   . 'quantity,payment_price,total_price';
        $sql       = "SELECT {$columns} FROM mall_order_item WHERE orderid = :orderid ORDER BY sub_orderid ASC";
        $params    = [
            ':orderid' => $orderId
        ];
        return Db::all($sql, $params);
    }

    /**
     * 获取单个子订单详情。
     *
     * @param  int   $subOrderId 子订单ID。
     * @return array
     */
    public static function getOrderItem($subOrderId)
    {
        $columns   = 'goodsid,goods_name,goods_image,productid,spec_val,market_price,sales_price,'
                   . 'quantity,payment_price,total_price,refund_status,reply_status,refund_status';
        $sql       = "SELECT {$columns} FROM mall_order_item WHERE sub_orderid = :sub_orderid";
        $params    = [
            ':sub_orderid' => $subOrderId
        ];
        return Db::one($sql, $params);
    }

    /**
     * 获取卖家用户订单详情。
     *
     * @param  int  $orderId 订单ID。
     * @return array
     */
    public static function getShopOrderDetail($orderId)
    {
        $where = [
            'orderid' => $orderId,
            'status'  => MallOrder::STATUS_YES
        ];
        $OrderModel  = new MallOrder();
        $orderDetail = $OrderModel->fetchOne([], $where);
        if (empty($orderDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '订单不存在');
        }
        $orderDetail['goods_list'] = self::getOrderItems($orderId);
        $logisticsInfo = self::getOrderExpressInfo($orderId);
        $orderDetail   = array_merge($orderDetail, $logisticsInfo);
        return $orderDetail;
    }

    /**
     * 获取买家用户订单详情。
     *
     * @param  int   $userid   用户ID。
     * @param  int   $orderId  订单ID。
     * @return array
     */
    public static function getUserOrderDetail($userid, $orderId)
    {
        $where = [
            'orderid' => $orderId,
            'userid'  => $userid,
            'status'  => MallOrder::STATUS_YES
        ];
        $OrderModel  = new MallOrder();
        $orderDetail = $OrderModel->fetchOne([], $where);
        if (empty($orderDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '订单不存在');
        }
        $orderDetail['goods_list'] = self::getOrderItems($orderId);
        $logisticsInfo = self::getOrderExpressInfo($orderId);
        $orderDetail   = array_merge($orderDetail, $logisticsInfo);
        return $orderDetail;
    }

    /**
     * 发货。
     * -- 1、可重复设置发货信息。
     * -- 2、发货后24小时内可修改发货信息。
     *
     * @param  int     $userid           用户ID。
     * @param  int     $orderId          订单ID。
     * @param  string  $logisticsCode    快递编码。
     * @param  string  $logisticsNumber  快递单号。
     * @return bool
     */
    public static function deliverGoods($userid, $orderId, $logisticsCode, $logisticsNumber)
    {
        if (strlen($logisticsCode) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '快递编码不能为空');
        }
        if (!Validator::is_len($logisticsCode, 1, 20, 1)) {
            YCore::exception(STATUS_SERVER_ERROR, '快递编码不正确');
        }
        if (strlen($logisticsNumber) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '快递单号不能为空');
        }
        if (!Validator::is_len($logisticsNumber, 1, 50, 1)) {
            YCore::exception(STATUS_SERVER_ERROR, '快递单号长度必须在1~50个字间');
        }
        $OrderModel = new MallOrder();
        $where = [
            'orderid' => $orderId
        ];
        $orderInfo = $OrderModel->fetchOne([], $where);
        if (empty($orderInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '订单不存在');
        }
        if ($orderInfo['order_status'] != MallOrder::ORDER_STATUS_PAY_OK && $orderInfo['order_status'] != MallOrder::ORDER_STATUS_DELIVER) {
            YCore::exception(STATUS_SERVER_ERROR, '已支付或已发货24小时内的才允许操作');
        }
        if ($orderInfo['order_status'] == MallOrder::ORDER_STATUS_DELIVER) {
            $diffTimestamp = time() - strtotime($orderInfo['shipping_time']);
            if ($diffTimestamp > 86400) {
                YCore::exception(STATUS_SERVER_ERROR, '发货超过24小时不能修改');
            }
        }
        $logisticsListDict = YCore::dict('logistics_list');
        if (!array_key_exists($logisticsCode, $logisticsListDict)) {
            YCore::exception(STATUS_SERVER_ERROR, '快递编号不正确');
        }
        $LogisticsModel = new MallLogistics();
        $where = [
            'orderid' => $orderId
        ];
        $logisticsInfo = $LogisticsModel->fetchOne([], $where);
        if (empty($logisticsInfo)) {
            $data = [
                'orderid'          => $orderId,
                'logistics_code'   => $logisticsCode,
                'logistics_number' => $logisticsNumber,
                'c_time'           => date('Y-m-d H:i:s', time()),
                'c_by'             => $userid
            ];
            $id = $LogisticsModel->insert($data);
            $ok = $id > 0 ? true : false;
        } else {
            $where = [
                'orderid' => $orderId
            ];
            $updata = [
                'logistics_code'   => $logisticsCode,
                'logistics_number' => $logisticsNumber,
                'u_time'           => date('Y-m-d H:i:s', time()),
                'u_by'             => $userid
            ];
            $ok = $LogisticsModel->update($updata, $where);
        }
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '操作失败');
        }
        // 更新订单发货状态。
        if ($orderInfo['order_status'] == MallOrder::ORDER_STATUS_PAY_OK) {
            $data = [
                'order_status'  => MallOrder::ORDER_STATUS_DELIVER,
                'shipping_time' => date('Y-m-d H:i:s', time()),
                'u_by'          => $userid,
                'u_time'        => date('Y-m-d H:i:s', time())
            ];
            $ok = $OrderModel->update($data, ['orderid' => $orderId]);
            if (!$ok) {
                YCore::exception(STATUS_SERVER_ERROR, '发货失败');
            }
        }
    }

    /**
     * 修改订单收货地址。
     * -- 1、发货前都可以修改地址。
     *
     * @param  int     $userid           用户ID。
     * @param  int     $orderId          订单ID。
     * @param  int     $districtId       区县ID。
     * @param  string  $receiverName     收货人姓名。
     * @param  string  $receiverAddress  收货人详细地址。
     * @param  string  $receiverMobile   收货人手机号。
     * @return bool
     */
    public static function adjustAddress($userid, $orderId, $districtId, $receiverName, $receiverAddress, $receiverMobile)
    {
        if (strlen($receiverName) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '收货人姓名必须填写');
        }
        if (!Validator::is_len($receiverName, 1, 10, true)) {
            YCore::exception(STATUS_SERVER_ERROR, '收货人姓名长度必须1~10个字符之间');
        }
        if (strlen($districtId) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '请选择区县');
        }
        if (strlen($receiverMobile) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '收货人手机号必须填写');
        }
        if (strlen($receiverAddress) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '收货人详细地址必须填写');
        }
        if (!Validator::is_mobilephone($receiverMobile)) {
            YCore::exception(STATUS_SERVER_ERROR, '收货人手机号不正确');
        }
        if (!Validator::is_len($receiverAddress, 1, 50, true)) {
            YCore::exception(STATUS_SERVER_ERROR, '收货详细地址长度必须1~50个字符之间');
        }
        $DistrictModel = new District();
        $districtInfo  = $DistrictModel->fetchOne([], ['district_id' => $districtId, 'status' => 1]);
        if (empty($districtInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '区县ID有误');
        }
        $provinceName = $districtInfo['province_name'];
        $cityName     = $districtInfo['city_name'];
        $districtName = $districtInfo['district_name'];
        $OrderModel   = new MallOrder();
        $where = [
            'orderid' => $orderId,
            'status'  => MallOrder::STATUS_YES
        ];
        $orderInfo = $OrderModel->fetchOne([], $where);
        if (empty($orderInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '订单不存在');
        }
        if ($orderInfo['order_status'] != MallOrder::ORDER_STATUS_PAY_OK) {
            YCore::exception(STATUS_SERVER_ERROR, '已付款的订单才允许修改收货信息');
        }
        $updata = [
            'receiver_province' => $provinceName,
            'receiver_city'     => $cityName,
            'receiver_district' => $districtName,
            'receiver_street'   => '',
            'receiver_address'  => $receiverAddress,
            'receiver_mobile'   => $receiverMobile,
            'u_by'              => $userid,
            'u_time'            => date('Y-m-d H:i:s', time())
        ];
        $ok = $OrderModel->update($updata, $where);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '操作失败');
        }
    }

    /**
     * 订单确认支支付。
     * -- 1、只允许在线支付的回调调用。
     * 
     * @param  int     $orderId      订单ID。
     * @param  string  $paymentCode  支付渠道编码。
     * @return bool
     */
    public static function paymentConfirmation($orderId, $paymentCode)
    {
        $OrderModel = new MallOrder();
        $orderInfo  = $OrderModel->fetchOne([], ['orderid' => $orderId, 'status' => MallOrder::STATUS_YES]);
        if (empty($orderInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '订单不存在或已经删除');
        }
        if ($orderInfo['order_status'] == MallOrder::ORDER_STATUS_DELIVER) {
            return true;
        }
        if ($orderInfo['order_status'] != MallOrder::ORDER_STATUS_WAIT_PAY) {
            YCore::exception(STATUS_SERVER_ERROR, '只允许操作未付款的订单');
        }
        $updateData = [
            'order_status'  => MallOrder::ORDER_STATUS_DELIVER,
            'u_by'          => 0, // 0 代表系统修改。
            'u_time'        => date('Y-m-d H:i:s', time()),
            'pay_time'      => date('Y-m-d H:i:s', time()),
            'pay_status'    => MallOrder::PAY_STATUS_YES,
            'payment_type'  => MallOrder::PAYMENT_TYPE_RMB,
            'payment_code'  => $paymentCode
        ];
        $where = [
            'orderid' => $orderId,
            'status'  => MallOrder::STATUS_YES
        ];
        $OrderModel->beginTransaction();
        $ok = $OrderModel->update($updateData, $where);
        if ($ok) {
            $logContent = '系统执行';
            self::writeLog(0, $orderId, 'pay', $logContent);
            $OrderModel->commit();
            return true;
        } else {
            $OrderModel->rollBack();
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 关闭订单。
     *
     * @param  int  $adminId  用户ID。
     * @param  int  $orderId  订单ID。
     * @return void
     */
    public static function closeOrder($userid, $orderId)
    {
        $OrderModel = new MallOrder();
        $orderInfo  = $OrderModel->fetchOne([], ['orderid' => $orderId, 'status' => MallOrder::STATUS_YES]);
        if (empty($orderInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '订单不存在或已经删除');
        }
        if ($orderInfo['order_status'] != MallOrder::ORDER_STATUS_WAIT_PAY) {
            YCore::exception(STATUS_SERVER_ERROR, '只允许关闭未付款的订单');
        }
        $updateData = [
            'order_status'  => MallOrder::ORDER_STATUS_CLOSED,
            'u_by'          => $userid,
            'u_time'        => date('Y-m-d H:i:s', time()),
            'closed_time'   => date('Y-m-d H:i:s', time())
        ];
        $where = [
            'orderid' => $orderId,
            'status'  => MallOrder::STATUS_YES
        ];
        $OrderModel->beginTransaction();
        $ok = $OrderModel->update($updateData, $where);
        if ($ok) {
            $ok = self::releaseOrderStock($orderId);
            if (!$ok) {
                $OrderModel->rollBack();
                YCore::exception(STATUS_SERVER_ERROR, '订单取消失败');
            }
            $logContent = '商家用户执行该操作';
            self::writeLog($userid, $orderId, 'closed', $logContent);
            $OrderModel->commit();
            return true;
        } else {
            $OrderModel->rollBack();
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 获取订单快递信息。
     *
     * @param  int  $orderId  订单ID。
     * @return array
     */
    public static function getOrderExpressInfo($orderId)
    {
        $where = [
            'orderid' => $orderId
        ];
        $columns = [
            'logistics_code',
            'logistics_number'
        ];
        $LogisticsModel = new MallLogistics();
        $logisticsInfo  = $LogisticsModel->fetchOne($columns, $where);
        if (empty($logisticsInfo)) {
            return [
                'logistics_code'   => '',
                'logistics_number' => ''
            ];
        } else {
            return $logisticsInfo;
        }
    }

    /**
     * 释放订单占用的库存。
     *
     * @param  int  $orderId  订单ID。
     * @return void
     */
    protected static function releaseOrderStock($orderId)
    {
        $orderItemWhere = [
            'orderid' => $orderId
        ];
        $OrderItemModel = new MallOrderItem();
        $orderItemList  = $OrderItemModel->fetchAll([], $orderItemWhere);
        foreach ($orderItemList as $item) {
            $ok = GoodsService::restoreProductStock($item['productid'], $item['quantity']);
            if (!$ok) {
                YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
            }
        }
    }

    /**
     * 订单操作日志。
     *
     * @param  int     $userid       用户ID。
     * @param  int     $orderId      订单ID。
     * @param  string  $actionType   操作类型。
     * @param  string  $logContent   日志内容。
     * @return bool
     */
    protected static function writeLog($userid, $orderId, $actionType, $logContent = '')
    {
        $orderOperationCode = YCore::dict('order_operation_code');
        if (!array_key_exists($actionType, $orderOperationCode)) {
            YCore::exception(STATUS_SERVER_ERROR, '操作类型不正确');
        }
        $data = [
            'orderid'      => $orderId,
            'action_type'  => $actionType,
            'log_content'  => $logContent,
            'userid'       => $userid,
            'c_time'       => date('Y-m-d H:i:s', time())
        ];
        $OrderLogModel = new MallOrderLog();
        $ok = $OrderLogModel->insert($data);
        return $ok > 0 ? true : false;
    }

    /**
     * 获取订单号。
     * -- 1、同网段的服务器产生的订单号不会重复。如：192.168.1.1 ~ 192.168.255.255
     * -- 2、多网段的服务器可能会产生重复的订单号。如果并发量不大的情况下，可以勉强使用。如果并发量太大，不要使用。
     * -- 3、订单号组成：前缀 + 时间戳(10位) + 微秒(6位) + 服务器IP编号(6位) + 用户ID(10位) = 订单号。
     *
     * @param  int     $userid   用户ID。订单号组成部分。用户来避免订单号重复。也可以通过订单号反解得到时间与用户ID等信息。
     * @param  string  $prefix   订单号前缀。不允许超过5个字符。
     * @return string
     */
    public static function getOrderSn($userid, $prefix = '')
    {
        if (strlen($prefix) > 5) {
            YCore::exception(STATUS_SERVER_ERROR, '订单号前缀不允许超过5个字符');
        }
        // [1]
        $microtime = microtime();
        list($usec, $sec) = explode(' ', $microtime);
        $usec = intval($usec * 1000000);
        $usec = sprintf('%06d', $usec);
        // [2]
        $serverIp     = date('Y-m-d H:i:s', time());
        $serverIpInt  = ip2long($serverIp);
        $serverNumber = $serverIpInt % 1000000;
        // [3]
        $userid  = sprintf('%010d', $userid);
        $orderSn = "{$sec}{$serverNumber}{$userid}{$usec}";
        return "{$prefix}{$orderSn}";
    }
}