<?php
/**
 * 商品详情接口。
 * @author fingerQin
 * @date 2019-08-07
 * @version 1.0.0
 */

namespace Apis\app\v100\Goods;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Mall\Goods;

class GoodsDetailApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $goodsId = $this->getInt('goods_id', 1);
        $token   = $this->getString('token', '');
        $userid  = Auth::getTokenUserId($token);
        $result  = Goods::detail($goodsId);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}