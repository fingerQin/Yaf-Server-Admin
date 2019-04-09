<?php
/**
 * 用户收货地址管理。
 * @author fingerQin
 * @date 2018-08-15
 */

namespace Services\User;

use Utils\YCore;
use Models\District;
use Models\UserAddress;
use finger\Validator;
use finger\Database\Db;

class Address extends \Services\AbstractBase
{
    /**
     * 获取用户收货地址详情。
     *
     * @param  int  $userid     用户ID。
     * @param  int  $addressId  收货地址ID。
     * @return array
     */
    public static function detail($userid, $addressId)
    {
        $where = [
            'userid'    => $userid,
            'addressid' => $addressId
        ];
        $columns = [
            'addressid',
            'realname',
            'mobilephone',
            'address',
            'district_id',
            'is_default'
        ];
        $AddressModel = new UserAddress();
        $detail = $AddressModel->fetchOne($columns, $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '收货地址不存在');
        }
        return $detail;
    }

    /**
     * 返回订单需要的格式。
     *
     * @param  int  $userid     用户 ID。
     * @param  int  $addreddId  地址 ID。
     * 
     * @return array
     */
    public static function getOrderFormat($userid, $addreddId)
    {
        $where = [
            'userid'    => $userid,
            'addressid' => $addreddId,
            'status'    => UserAddress::STATUS_YES
        ];
        $AddressModel = new UserAddress();
        $addressInfo  = $AddressModel->fetchOne([], $where);
        if (empty($addressInfo)) {
            YCore::exception(STATUS_ERROR, '您选择的收货地址已经失效');
        }
        $DistrictModel = new District();
        $districtInfo  = $DistrictModel->fetchOne([], [
            'districtid' => $addressInfo['districtid'], 
            'status'     => District::STATUS_YES
        ]);
        if (empty($districtInfo)) {
            YCore::exception(STATUS_ERROR, '您的收货地址的区县已经失效');
        }
        $provinceName = $districtInfo['province_name'];
        $cityName     = $districtInfo['city_name'];
        $districtName = $districtInfo['district_name'];
        $realname     = $addressInfo['realname'];
        $address      = $addressInfo['address'];
        $mobilephone  = $addressInfo['mobile'];
        $data = [
            'realname'      => $realname,
            'province_name' => $provinceName,
            'city_name'     => $cityName,
            'district_name' => $districtName,
            'address'       => $address,
            'mobilephone'   => $mobilephone
        ];
        return $data;
    }

    /**
     * 添加收货地址。
     *
     * @param  int     $userid      用户ID。
     * @param  string  $realname    真实姓名。
     * @param  string  $mobile      手机号码。
     * @param  int     $districtId  地区ID。
     * @param  string  $address     街道详细地址。
     * @return int
     */
    public static function add($userid, $realname, $mobile, $districtId, $address)
    {
        self::checkConsignee($realname);
        self::checkMobilephone($mobile);
        self::checkDistrictId($districtId);
        self::checkAddress($address);
        $DistrictModel = new District();
        $districtInfo  = $DistrictModel->fetchOne([], [
            'districtid' => $districtId, 
            'status'     => District::STATUS_YES
        ]);
        if (empty($districtInfo) || $districtInfo['region_type'] < 3) {
            YCore::exception(STATUS_ERROR, '地址有误');
        }
        $addressCount = self::getUserAddressCount($userid);
        if ($addressCount >= USER_ADDRESS_MAX_COUNT) {
            YCore::exception(STATUS_SERVER_ERROR, "最多允许创建" . USER_ADDRESS_MAX_COUNT . "个收货地址");
        }
        $data = [
            'realname'   => $realname,
            'mobile'     => $mobile,
            'userid'     => $userid,
            'districtid' => $districtId,
            'address'    => $address,
            'status'     => UserAddress::STATUS_YES,
            'c_time'     => date('Y-m-d H:i:s', time())
        ];
        $UserAddressModel = new UserAddress();
        $addressId        = $UserAddressModel->insert($data);
        if ($addressId == 0) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
        return $addressId;
    }

    /**
     * 获取用户收货地址数量。
     *
     * @param  int $userid  用户ID。
     * @return int
     */
    public static function getUserAddressCount($userid)
    {
        $AddressModel = new UserAddress();
        $where = [
            'userid' => $userid,
            'status' => UserAddress::STATUS_YES
        ];
        return $AddressModel->count($where);
    }

    /**
     * 编辑收货地址。
     *
     * @param  int     $userid      用户ID。
     * @param  int     $addressId   收货地址ID。
     * @param  string  $realname    真实姓名。
     * @param  string  $mobile      手机号码。
     * @param  int     $districtId  地区ID。
     * @param  string  $address     街道详细地址。
     * @return bool
     */
    public static function edit($userid, $addressId, $realname, $mobile, $districtId, $address)
    {
        self::checkConsignee($realname);
        self::checkMobilephone($mobile);
        self::checkDistrictId($districtId);
        self::checkAddress($address);
        $DistrictModel = new District();
        $districtInfo  = $DistrictModel->fetchOne([], [
            'districtid' => $districtId, 
            'status'     => District::STATUS_YES
        ]);
        if (empty($districtInfo) || $districtInfo['region_type'] < 3) {
            YCore::exception(STATUS_ERROR, '地址有误');
        }
        self::isExistUserAddress($addressId, $userid);
        $UserAddressModel = new UserAddress();
        $data = [
            'realname'   => $realname,
            'districtid' => $districtId,
            'mobile'     => $mobile,
            'status'     => UserAddress::STATUS_YES,
            'u_time'     => date('Y-m-d H:i:s', time()),
            'address'    => $address
        ];
        $ok = $UserAddressModel->update($data, ['addressid' => $addressId]);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
        return true;
    }

    /**
     * 删除收货地址。
     *
     * @param  int  $userid     用户ID。
     * @param  int  $addressId  收货地址ID。
     * @return bool
     */
    public static function delete($userid, $addressId)
    {
        self::isExistUserAddress($addressId, $userid);
        $UserAddressModel = new UserAddress();
        $data = [
            'status' => UserAddress::STATUS_DELETED,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $where = [
            'addressid' => $addressId,
            'userid'    => $userid
        ];
        $ok = $UserAddressModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
        return true;
    }

    /**
     * 获取用户所有收货地址。
     *
     * @param  int  $userid  用户ID。
     * @return array
     */
    public static function all($userid)
    {
        $sql = 'SELECT a.addressid,a.realname,a.mobile,a.districtid,a.address,'
             . 'b.province_name,b.city_name,b.district_name,b.street_name '
             . 'FROM finger_user_address AS a INNER JOIN '
             . 'finger_district AS b ON(a.districtid=b.districtid) '
             . 'WHERE a.userid = :userid AND a.status = :status';
        $params = [
            ':userid' => $userid,
            ':status' => UserAddress::STATUS_YES
        ];
        return Db::all($sql, $params);
    }

    /**
     * 设置默认收货地址。
     *
     * @param  int  $userid     用户ID。
     * @param  int  $addressId  收货地址ID。
     * @return bool
     */
    public static function setDefault($userid, $addressId)
    {
        self::isExistUserAddress($addressId, $userid);
        $UserAddressModel = new UserAddress();
        $UserAddressModel->update(['is_default' => UserAddress::DEFAULT_NO], [
            'userid' => $userid,
            'status' => UserAddress::STATUS_YES
        ]);
        $data = [
            'is_default' => UserAddress::DEFAULT_YES,
            'u_time'     => date('Y-m-d H:i:s', time())
        ];
        $where = [
            'addressid' => $addressId,
            'userid'    => $userid
        ];
        $ok = $UserAddressModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 检查收货人姓名格式。
     * @param  string  $consignee  收货人姓名。
     * @return bool
     */
    public static function checkConsignee($consignee)
    {
        $data  = ['consignee' => $consignee];
        $rules = [
            'consignee' => '收货人姓名|require|len:2:10:1'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查收货人手机号格式。
     * @param  string  $mobile  收货人姓名。
     * @return bool
     */
    public static function checkMobilephone($mobile)
    {
        $data  = ['mobilephone' => $mobile];
        $rules = [
            'mobilephone' => '手机号码|require|mobilephone'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查收货人详细地址。
     * @param  string  $address  收货人详细地址。
     * @return bool
     */
    public static function checkAddress($address)
    {
        $data  = ['address' => $address];
        $rules = [
            'address' => '详细地址|require|len:1:50:1'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查收货人所在地区。
     * @param  string  $districtId  收货人所在地区ID。
     * @return bool
     */
    public static function checkDistrictId($districtId)
    {
        $data  = ['districtid' => $districtId];
        $rules = [
            'districtid' => '所在地区|require|number_between:1:100000'
        ];
        Validator::valido($data, $rules);
        $DistrictModel = new District();
        $districtInfo  = $DistrictModel->fetchOne([], [
            'districtid' => $districtId,
            'status'     => District::STATUS_YES
        ]);
        if (empty($districtInfo)) {
            YCore::exception(STATUS_ERROR, '区县ID有误');
        }
    }

    /**
     * 用户收货地址是否存在。
     * @param  int     $addressId  收货地址ID。
     * @param  int     $userid     用户ID。
     * @param  string  $errMsg     错误时的提示。
     * @return bool
     */
    public static function isExistUserAddress($addressId, $userid, $errMsg = '')
    {
        $where = [
            'addressid' => $addressId,
            'status'    => UserAddress::STATUS_YES,
            'userid'    => $userid
        ];
        $UserAddressModel = new UserAddress();
        $addressInfo      = $UserAddressModel->fetchOne([], $where, '', '', true);
        if (empty($addressInfo)) {
            $errMsg = (strlen($errMsg) > 0) ? $errMsg : '收货地址不存在';
            YCore::exception(STATUS_SERVER_ERROR, $errMsg);
        }
    }
}