<?php
/**
 * 商品列表接口。
 * @author fingerQin
 * @date 2019-08-07
 * @version 1.0.0
 */

namespace Apis\app\v100\Goods;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Mall\Goods;

class GoodsListApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $keyword    = $this->getString('keyword', '');
        $catId      = $this->getInt('cat_id', -1);
        $page       = $this->getInt('page', 1);
        $startPrice = $this->getInt('start_price', -1);
        $endPrice   = $this->getInt('end_price', -1);
        $token      = $this->getString('token', '');
        $userid     = Auth::getTokenUserId($token);
        $result     = Goods::list($keyword, $catId, $startPrice, $endPrice, $page, 20);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}