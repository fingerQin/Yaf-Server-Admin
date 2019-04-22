<?php
/**
 * 用户收货地址列表接口。
 * 
 * @author fingerQin
 * @date 2019-04-09
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\User\Address;

class UserAddressListApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token    = $this->getString('token', '');
        $userinfo = Auth::checkAuth($token);
        $detail   = Address::all($userinfo['userid']);
        $this->render(STATUS_SUCCESS, 'success', $detail);
    }
}