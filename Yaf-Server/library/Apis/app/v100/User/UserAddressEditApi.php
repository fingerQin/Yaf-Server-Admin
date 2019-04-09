<?php
/**
 * 用户编辑收货地址接口。
 * 
 * @author fingerQin
 * @date 2019-04-09
 * @version 1.0.0
 */

namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\User\Address;

class UserAddressEditApi extends AbstractApi
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
        $addressId  = $this->getInt('addressid');
        $realname   = $this->getString('realname', '');
        $mobile     = $this->getString('mobile', '');
        $districtId = $this->getInt('district_id', 0);
        $address    = $this->getString('address', '');
        Address::edit($userinfo['userid'], $addressId, $realname, $mobile, $districtId, $address);
        $this->render(STATUS_SUCCESS, '修改成功');
    }
}