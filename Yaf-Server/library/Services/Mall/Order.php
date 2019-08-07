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
use Utils\YCache;
use Models\District;
use Models\MallOrder;
use Models\MallOrderItem;
use Models\MallGoods;
use Models\MallProduct;
use Models\MallOrderLog;
use Models\MallLogistics;
use Models\GoldConsume;
use Services\AbstractBase;
use Services\User\Address;
use Services\Gold\Gold;

class Order extends AbstractBase
{
    /**
     * 用户兑换订单列表。
     *
     * @param  int     $userid       用户ID.
     * @param  string  $orderSn      订单号。
     * @param  int     $orderStatus  订单状态。
     * @param  string  $startTime    成交时间开始。
     * @param  string  $endTime      成效时间结束。
     * @param  int     $page         当前页码。
     * @param  int     $count        每页显示条数。
     * @return array
     */
    public static function list($userid, $orderSn = '', $orderStatus = -1, $startTime = '', $endTime = '', $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM mall_order ';
        $columns   = 'orderid, order_sn, total_price, pay_time,'
                   . 'order_status, shipping_time, done_time, closed_time, '
                   . 'receiver_name, receiver_province, receiver_city, receiver_district,'
                   . 'receiver_street, receiver_address, receiver_mobile, c_time';
        $where     = ' WHERE userid = :userid AND status = :status ';
        $params    = [
            ':userid' => $userid,
            ':status' => MallOrder::STATUS_YES
        ];
        if (strlen($orderSn) > 0) {
            $where .= ' AND order_sn = :order_sn ';
            $params[':order_sn'] = $orderSn;
        }
        if ($orderStatus != -1) {
            $where .= ' AND order_status = :order_status ';
            $params[':order_status'] = $orderStatus;
        }
        if (strlen($startTime) > 0) {
            if (!Validator::is_date($startTime)) {
                YCore::exception(STATUS_SERVER_ERROR, '成交时间格式不正确');
            }
            $where .= ' AND c_time >= :start_time ';
            $params[':start_time'] = $startTime;
        }
        if (strlen($endTime) > 0) {
            if (!Validator::is_date($endTime)) {
                YCore::exception(STATUS_SERVER_ERROR, '成交时间格式不正确');
            }
            $where .= ' AND c_time <= :end_time ';
            $params[':end_time'] = $endTime;
        }
        $orderBy   = ' ORDER BY orderid DESC ';
        $sql       = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $item['order_status_label'] = MallOrder::$orderStatusDict[$item['order_status']];
            $item['goods_list']         = self::getOrderItems($item['orderid']);
            $list[$key]                 = $item;
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
        $columns = 'goodsid,goods_name,goods_image,productid,spec_val,market_price,sales_price,'
                 . 'quantity,payment_price,total_price';
        $sql     = "SELECT {$columns} FROM mall_order_item WHERE orderid = :orderid ORDER BY sub_orderid ASC";
        $params  = [
            ':orderid' => $orderId
        ];
        return Db::all($sql, $params);
    }

    /**
     * 获取单个子订单详情。
     *
     * @param  int  $subOrderId  子订单ID。
     * @return array
     */
    public static function getOrderItem($subOrderId)
    {
        $columns = 'goodsid,goods_name,goods_image,productid,spec_val,market_price,sales_price,'
                 . 'quantity,payment_price,total_price,refund_status,reply_status,refund_status';
        $sql     = "SELECT {$columns} FROM mall_order_item WHERE sub_orderid = :sub_orderid";
        $params  = [
            ':sub_orderid' => $subOrderId
        ];
        return Db::one($sql, $params);
    }

    /**
     * 获取买家订单详情。
     *
     * @param  int  $userid   用户ID。
     * @param  int  $orderId  订单ID。
     * @return array
     */
    public static function detail($userid, $orderId)
    {
        $where = [
            'orderid' => $orderId,
            'userid'  => $userid,
            'status'  => MallOrder::STATUS_YES
        ];
        $columns     = [
            'orderid', 'order_sn', 'total_price', 'pay_time',
            'order_status', 'shipping_time', 'done_time', 'closed_time',
            'receiver_name', 'receiver_province', 'receiver_city', 'receiver_district',
            'receiver_street', 'receiver_address', 'receiver_mobile', 'c_time'
        ];
        $OrderModel  = new MallOrder();
        $orderDetail = $OrderModel->fetchOne($columns, $where);
        if (empty($orderDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '订单不存在');
        }
        $orderDetail['order_status_label'] = MallOrder::$orderStatusDict[$orderDetail['order_status']];
        $orderDetail['goods_list']         = self::getOrderItems($orderId);
        $logisticsInfo                     = self::getExpressInfo($userid, $orderId);
        return array_merge($orderDetail, $logisticsInfo);
    }

    /**
     * 用户提交订单。
     * -- Example start --
     * $data = [
     *      'userid'     => '用户ID',
     *      'goods_list' => '商品列表',
     *      'addressid'  => '收货地址ID。',
     * ];
     *
     * $goodsList = [
     *  [
     *      'goodsid'   => '商品ID',
     *      'productid' => '货品ID',
     *      'quantity'  => '购买数量',
     *  ],
     *  [
     *      'goodsid'   => '商品ID',
     *      'productid' => '货品ID',
     *      'quantity'  => '购买数量',
     *  ],
     *  ......
     * ];
     * -- Example end --
     *
     * @param  array  $data  订单数据。
     * @return int 订单ID。
     */
    public static function submit($data)
    {
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '购买信息有误');
        }
        if (!isset($data['goods_list']) || empty($data['goods_list'])) {
            YCore::exception(STATUS_SERVER_ERROR, '没有购买任何宝贝');
        }
        if (!isset($data['addressid'])) {
            YCore::exception(STATUS_SERVER_ERROR, '收货地址有误');
        }
        if (count($data['goods_list']) > 10) {
            YCore::exception(STATUS_SERVER_ERROR, '一次最多只允许兑换10个宝贝');
        }
        $addressInfo = Address::getOrderFormat($data['userid'], $data['addressid']);
        // 准备订单需要的信息。
        $orderData = [
            'userid' => $data['userid'],
        ];
        // 合并地址信息。
        $orderData = array_merge($orderData, $addressInfo);
        // 准备开启事务。
        Db::beginTransaction();
        $orderData['goods_list'] = $data['goods_list'];
        try {
            $orderId = self::submitShopOrder($orderData);
        } catch (\Exception $e) {
            Db::rollBack();
            YCore::exception($e->getCode(), $e->getMessage());
        }
        Db::commit();
        return $orderId;
    }

    /**
     * 用户下单。
     * -- Example start --
     * $data = 
     * [
     *      'userid'        => '用户ID',
     *      'goods_list'    => '商品列表',
     *      'realname'      => '收货人真实姓名',
     *      'mobilephone'   => '收货人手机号码',
     *      'address'       => '收货人详细地址',
     *      'province_name' => '省名称',
     *      'city_name'     => '市名称',
     *      'district_name' => '区县名称',
     * ];
     *
     * $goodsList = 
     * [
     *     [
     *        'goodsid'   => '商品ID',
     *        'productid' => '货品ID',
     *        'quantity'  => '购买数量',
     *     ],
     *     [
     *        'goodsid'   => '商品ID',
     *        'productid' => '货品ID',
     *        'quantity'  => '购买数量',
     *     ],
     *  ......
     * ];
     * -- Example end --
     *
     * @param  array  $data  订单信息。
     * @return int 订单ID
     */
    protected static function submitShopOrder($data)
    {
        $insertData = [
            'userid'            => $data['userid'],
            'order_sn'          => self::getOrderSn($data['userid'], 'SN'),
            'total_price'       => 0,
            'order_status'      => MallOrder::ORDER_STATUS_PAY_OK,
            'receiver_name'     => $data['realname'],
            'receiver_province' => $data['province_name'],
            'receiver_city'     => $data['city_name'],
            'receiver_district' => $data['district_name'],
            'receiver_street'   => '', // 暂时不支持四级地址。
            'receiver_address'  => $data['address'],
            'receiver_mobile'   => $data['mobilephone'],
            'status'            => MallOrder::STATUS_YES,
            'c_time'            => date('Y-m-d H:i:s', time()),
            'c_by'              => $data['userid']
        ];
        $OrderModel = new MallOrder();
        $orderId    = $OrderModel->insert($insertData);
        if ($orderId) {
            try {
                $priceInfo = self::addOrderItem($data['userid'], $orderId, $data['goods_list']);
            } catch (\Exception $e) {
                YCore::exception($e->getCode(), $e->getMessage());
            }
        }
        $updateData = [];
        Gold::consume($data['userid'], $priceInfo['payment_price'], GoldConsume::CONSUME_TYPE_CUT, GoldConsume::CONSUME_CODE_EXCHANGE);
        $updateData['pay_time']      = date('Y-m-d H:i:s', time());
        $updateData['order_status']  = MallOrder::ORDER_STATUS_PAY_OK;
        $updateData['total_price']   = $priceInfo['payment_price'];
        $ok = $OrderModel->update($updateData, ['orderid' => $orderId]);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试'); // 此处应该给出日志输出。
        }
        return $orderId;
    }

    /**
     * 添加订单购买的商品明细。
     * -- Example start --
     * $goodsList = [
     *  [
     *      'goodsid'   => '商品ID',
     *      'productid' => '货品ID',
     *      'quantity'  => '购买数量',
     *  ],
     *  [
     *      'goodsid'   => '商品ID',
     *      'productid' => '货品ID',
     *      'quantity'  => '购买数量',
     *  ],
     *  ......
     * ];
     * -- Example end --
     *
     * @param  int    $userid     购买人用户的ID。
     * @param  int    $orderId    订单ID。
     * @param  array  $goodsList  购买的商品列表。
     *
     * @return array 返回订单金币与实付金币。
     */
    protected static function addOrderItem($userid, $orderId, $goodsList)
    {
        $totalPrice     = 0;
        $paymentPrice   = 0;
        $OrderItemModel = new MallOrderItem();
        $GoodsModel     = new MallGoods();
        $ProductModel   = new MallProduct();
        foreach ($goodsList as $goods) {
            if (!isset($goods['goodsid'])) {
                YCore::exception(STATUS_SERVER_ERROR, '购买的商品数据异常');
            }
            if (!isset($goods['productid'])) {
                YCore::exception(STATUS_SERVER_ERROR, '货品数据异常');
            }
            if (!isset($goods['quantity']) || !Validator::is_integer($goods['quantity']) || $goods['quantity'] <= 0) {
                YCore::exception(STATUS_SERVER_ERROR, '商品购买数量有误');
            }
            $goodsInfo = $GoodsModel->fetchOne([], ['goodsid' => $goods['goodsid']]);
            if (empty($goodsInfo)) {
                YCore::exception(STATUS_SERVER_ERROR, '商品不存在或已经删除');
            }
            if ($goodsInfo['status'] != MallGoods::STATUS_YES) {
                YCore::exception(STATUS_SERVER_ERROR, "[{$goodsInfo['goods_name']}]已经删除");
            }
            if ($goodsInfo['marketable'] != MallGoods::STATUS_YES) {
                YCore::exception(STATUS_SERVER_ERROR, "[{$goodsInfo['goods_name']}]已经下架");
            }
            $ProductModel = new MallProduct();
            $productInfo  = $ProductModel->fetchOne([], ['productid' => $goods['productid'], 'status' => MallProduct::STATUS_YES]);
            if (empty($productInfo)) {
                YCore::exception(STATUS_SERVER_ERROR, "[{$goodsInfo['goods_name']}]已经下线");
            }
            if ($productInfo['stock'] < $goods['quantity']) {
                YCore::exception(STATUS_SERVER_ERROR, "[{$goodsInfo['goods_name']}]库存不足");
            }
            $_totalPrice   = $productInfo['market_price'] * $goods['quantity'];
            $_paymentPrice = $productInfo['sales_price'] * $goods['quantity'];
            $totalPrice   += $_totalPrice;
            $paymentPrice += $_paymentPrice;
            $data = [
                'orderid'       => $orderId,
                'goodsid'       => $goodsInfo['goodsid'],
                'goods_name'    => $goodsInfo['goods_name'],
                'goods_image'   => $goodsInfo['goods_img'],
                'productid'     => $productInfo['productid'],
                'spec_val'      => $productInfo['spec_val'],
                'market_price'  => $productInfo['market_price'],
                'sales_price'   => $productInfo['sales_price'],
                'quantity'      => $goods['quantity'],
                'c_time'        => date('Y-m-d H:i:s', time()),
                'c_by'          => $userid,
                'payment_price' => $_totalPrice,
                'total_price'   => $_totalPrice
            ];
            $ok = $OrderItemModel->insert($data);
            if (!$ok) {
                YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
            }
            $ok = Goods::deductionProductStock($productInfo['productid'], $goods['quantity']);
            if (!$ok) {
                YCore::exception(STATUS_SERVER_ERROR, "《{$goodsInfo['goods_name']}》库存不足");
            }
        }
        return [
            'total_price'   => $totalPrice,
            'payment_price' => $paymentPrice
        ];
    }

    /**
     * 修改订单收货地址。
     * -- 1、发货前都可以修改地址。
     *
     * @param  int     $userid           用户ID。
     * @param  int     $orderId          订单ID。
     * @param  int     $districtId       区县ID。
     * @param  string  $receiverName     收货人姓名。
     * @param  string  $receiverMobile   收货人手机号。
     * @param  string  $receiverAddress  收货人详细地址。
     * @return bool
     */
    public static function adjustAddress($userid, $orderId, $districtId, $receiverName, $receiverMobile, $receiverAddress)
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
        $districtInfo  = $DistrictModel->fetchOne([], ['districtid' => $districtId, 'status' => District::STATUS_YES]);
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
     * 确认收货收货接口。
     *
     * @param  int  $userid   用户ID。
     * @param  int  $orderId  订单ID。
     * @return void
     */
    public static function confirm($userid, $orderId)
    {
        $OrderModel = new MallOrder();
        $orderInfo  = $OrderModel->fetchOne([], ['orderid' => $orderId, 'status' => MallOrder::STATUS_YES]);
        if (empty($orderInfo) || $orderInfo['userid'] != $userid) {
            YCore::exception(STATUS_SERVER_ERROR, '订单不存在或已经删除');
        }
        if ($orderInfo['order_status'] == MallOrder::ORDER_STATUS_CLOSED) {
            YCore::exception(STATUS_SERVER_ERROR, '该订单已关闭');
        }
        if ($orderInfo['order_status'] == MallOrder::ORDER_STATUS_SUCCESS) {
            YCore::exception(STATUS_SERVER_ERROR, '请勿重复操作');
        }
        if ($orderInfo['order_status'] != MallOrder::ORDER_STATUS_DELIVER) {
            YCore::exception(STATUS_SERVER_ERROR, '只允许已发货的订单');
        }
        $datetime   = date('Y-m-d H:i:s', time());
        $updateData = [
            'order_status' => MallOrder::ORDER_STATUS_SUCCESS,
            'u_by'         => $userid,
            'u_time'       => $datetime,
            'done_time'    => $datetime
        ];
        $where = [
            'orderid' => $orderId,
            'status'  => MallOrder::STATUS_YES
        ];
        $ok = $OrderModel->update($updateData, $where);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
        self::writeLog($userid, $orderId, 'canceled');
    }

    /**
     * 获取订单快递信息。
     *
     * @param  int  $userid   用户ID。
     * @param  int  $orderId  订单ID。
     * @return array
     */
    public static function getExpressInfo($userid, $orderId)
    {
        $where   = ['orderid' => $orderId];
        $columns = ['logistics_code', 'logistics_number'];
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
     * 订单操作日志。
     *
     * @param  int     $userid      用户ID。
     * @param  int     $orderId     订单ID。
     * @param  string  $actionType  操作类型。
     * @param  string  $logContent  日志内容。
     * @return bool
     */
    protected static function writeLog($userid, $orderId, $actionType, $logContent = '')
    {
        $data = [
            'orderid'     => $orderId,
            'action_type' => $actionType,
            'log_content' => $logContent,
            'userid'      => $userid,
            'c_time'      => date('Y-m-d H:i:s', time())
        ];
        $OrderLogModel = new MallOrderLog();
        $ok = $OrderLogModel->insert($data);
        return $ok > 0 ? true : false;
    }

    /**
     * 获取订单号。
     *
     * @param  int     $userid  用户ID。预留。
     * @param  string  $prefix  订单号前缀。不允许超过5个字符。
     * @return string
     */
    public static function getOrderSn($userid, $prefix = '')
    {
        if (strlen($prefix) > 5) {
            YCore::exception(STATUS_SERVER_ERROR, '订单号前缀不允许超过5个字符');
        }
        $time     = time();
        $cacheKey = "order_sn_" . date('Y-m-d', $time);
        $incr     = YCache::incr($cacheKey);
        return "{$prefix}" . date('Ymd', $time) . sprintf('%010d', $incr);
    }
}