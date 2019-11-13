<?php
/**
 * 用户增加收货地址接口。
 * 
 * @author fingerQin
 * @date 2019-04-09
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\User\Address;

class UserAddressAddApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token      = $this->getString('token', '');
        $userinfo   = Auth::checkAuth($token);
        $realname   = $this->getString('realname', '');
        $mobile     = $this->getString('mobile', '');
        $districtId = $this->getInt('district_id', 0);
        $address    = $this->getString('address', '');
        $addressId  = Address::add($userinfo['userid'], $realname, $mobile, $districtId, $address);
        $this->render(STATUS_SUCCESS, '添加成功', ['address_id' => $addressId]);
    }
}