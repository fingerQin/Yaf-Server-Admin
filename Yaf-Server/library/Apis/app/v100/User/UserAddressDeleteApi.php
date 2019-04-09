<?php
/**
 * 用户删除收货地址接口。
 * 
 * @author fingerQin
 * @date 2019-04-09
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\User\Address;

class UserAddressDeleteApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token     = $this->getString('token', '');
        $userinfo  = Auth::checkAuth($token);
        $addressId = $this->getInt('addressid');
        Address::delete($userinfo['userid'], $addressId);
        $this->render(STATUS_SUCCESS, '删除成功');
    }
}