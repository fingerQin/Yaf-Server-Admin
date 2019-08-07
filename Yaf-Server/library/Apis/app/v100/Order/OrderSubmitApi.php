<?php
/**
 * 商品兑换接口。
 * @author fingerQin
 * @date 2019-08-07
 * @version 1.0.0
 */

namespace Apis\app\v100\Order;

use Utils\YCore;
use Apis\AbstractApi;
use Services\User\Auth;
use Services\Mall\Order;

class OrderSubmitApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $addressId    = $this->getInt('address_id', -1);
        $goodsList    = $this->getString('goods_list', '');
        $token        = $this->getString('token', '');
        $userinfo     = Auth::checkAuth($token);
        $data = [
            'userid'     => $userinfo['userid'],
            'goods_list' => self::parsetGoodsListParam($goodsList),
            'addressid'  => $addressId
        ];
        $orderId = Order::submit($data);
        $this->render(STATUS_SUCCESS, '兑换成功', ['order_id' => $orderId]);
    }

    /**
     * 解析接口的产品列表参数。
     *
     * @param  string  $goodsList  产品列表参数。格式：1,1,1|2,2,2 产品ID,货品ID,购买数量|产品ID,货品ID,购买数量。
     *
     * @return array
     */
    protected function parsetGoodsListParam($goodsList)
    {
        if (strlen($goodsList) == 0) {
            YCore::exception(STATUS_SERVER_ERROR, 'goods_list 参数有误');
        }
        $result     = [];
        $goodsGroup = explode('|', $goodsList);
        foreach ($goodsGroup as $group) {
            $groupInfo = explode(',', $group);
            if (count($groupInfo) != 3) {
                YCore::exception(STATUS_SERVER_ERROR, 'goods_list 参数有误');
            }
            $result[] = [
                'goodsid'   => $groupInfo[0],
                'productid' => $groupInfo[1],
                'quantity'  => $groupInfo[2]
            ];
        }
        return $result;
    }
}