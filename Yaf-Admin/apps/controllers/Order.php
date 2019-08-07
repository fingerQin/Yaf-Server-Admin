<?php
/**
 * 积分商城之订单列表。
 * @author fingerQin
 * @date 2019-08-07
 */

use finger\Paginator;
use Services\Mall\Order;

class OrderController extends \Common\controllers\Admin
{
    /**
     * 订单列表。
     */
    public function listAction()
    {
        $goodsId        = $this->getString('goods_id', -1);
        $mobile         = $this->getString('mobile', '');
        $receiverName   = $this->getString('receiver_name', '');
        $receiverMobile = $this->getString('receiver_mobile', '');
        $orderStatus    = $this->getInt('order_status', -1);
        $orderSn        = $this->getString('order_sn', '');
        $startTime      = $this->getString('start_time', date('2018-01-01 00:00:00'));
        $endTime        = $this->getString('endTime', date('Y-m-d 23:59:59'));
        $page           = $this->getString('page', 1);
        $result         = Order::list($goodsId, $mobile, $receiverName, $receiverMobile, $orderSn, $orderStatus, $startTime, $endTime, $page, 20);
        $paginator      = new Paginator($result['total'], 20);
        $pageHtml       = $paginator->backendPageShow();
        $this->assign('page_html', $pageHtml);
        $this->assign('list', $result['list']);
        $this->assign('goods_id', $goodsId);
        $this->assign('receiver_name', $receiverName);
        $this->assign('receiver_mobile', $receiverMobile);
        $this->assign('order_sn', $orderSn);
        $this->assign('order_status', $orderStatus);
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('mobile', $mobile);
    }

    /**
     * 发货。
     */
    public function deliverGoodsAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $orderId         = $this->getInt('orderid');
            $logisticsCode   = $this->getString('logistics_code');
            $logisticsNumber = $this->getString('logistics_number');
            Order::deliverGoods($this->adminId, $orderId, $logisticsCode, $logisticsNumber);
            $this->json(true, '发货成功');
        }
    }

    /**
     * 订单收货地址调整(未发货前仅能变更一次)。
     */
    public function adjustAddressAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $orderId         = $this->getInt('order_id');
            $districtId      = $this->getInt('district_id');
            $receiverName    = $this->getString('receiver_name');
            $receiverAddress = $this->getString('receiver_address');
            $receiverMobile  = $this->getString('receiver_mobile');
            $receiverZip     = $this->getString('receiver_zip');
            Order::adjustAddress($this->adminId, $orderId, $districtId, $receiverName, $receiverAddress, $receiverMobile, $receiverZip);
            $this->json(true, '调价成功');
        }
        $orderId     = $this->getInt('order_id');
        $orderDetail = Order::getShopOrderDetail($orderId);
        $this->assign('order_detail', $orderDetail);
    }

    /**
     * 关闭订单。
     */
    public function closeAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $orderId = $this->getInt('order_id');
            Order::closeOrder($this->adminId, $orderId);
            $this->json(true, '操作成功');
        }
    }
}