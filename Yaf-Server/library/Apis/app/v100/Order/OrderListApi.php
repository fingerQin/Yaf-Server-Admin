<?php
/**
 * 订单列表接口。
 * @author fingerQin
 * @date 2019-08-07
 * @version 1.0.0
 */

namespace Apis\app\v100\Order;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Mall\Order;

class OrderListApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $orderSn     = $this->getString('order_sn', '');
        $orderStatus = $this->getInt('order_status', -1);
        $startTime   = $this->getString('start_time', '');
        $endTime     = $this->getString('end_time', '');
        $page        = $this->getInt('page', 1);
        $token       = $this->getString('token', '');
        $userinfo    = Auth::checkAuth($token);
        $result      = Order::list($userinfo['userid'], $orderSn, $orderStatus, $startTime, $endTime, $page, 20);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}