<?php
/**
 * 订单详情接口。
 * @author fingerQin
 * @date 2019-08-07
 * @version 1.0.0
 */

namespace Apis\app\v100\Order;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Mall\Order;

class OrderDetailApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $orderId  = $this->getInt('order_id', 0);
        $token    = $this->getString('token', '');
        $userinfo = Auth::checkAuth($token);
        $detail   = Order::detail($userinfo['userid'], $orderId);
        $this->render(STATUS_SUCCESS, 'success', $detail);
    }
}