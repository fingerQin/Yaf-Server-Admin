<?php
/**
 * 用户收货地址管理。
 * @author fingerQin
 * @date 2016-04-08
 */

namespace services\user;

use finger\Validator;
use finger\Utils\YCore;
use models\District;
use models\MallUserAddress;

class UserAddress extends \services\core\Base
{
    /**
     * 获取用户收货地址详情。
     *
     * @param  int  $userid     用户ID。
     * @param  int  $addressId  收货地址ID。
     * @return array
     */
    public static function getAddressDetail($userid, $addressId)
    {
        $where = [
            'user_id'    => $userid,
            'address_id' => $addressId
        ];
        $columns = [
            'address_id',
            'realname',
            'mobilephone',
            'address',
            'district_id',
            'is_default'
        ];
        $AddressModel = new MallUserAddress();
        $detail = $AddressModel->fetchOne([], $where);
        if (empty($detail)) {
            YCore::exception(STATUS_ERROR, '收货地址不存在');
        }
        return $detail;
    }

    /**
     * 根据用户提交的地址返回指定格式的地址信息。
     * -- Example start --
     * $data = [
     *      'user_id'     => '用户ID',
     *      'address_id'  => '用户地址ID',
     *      'realname'    => '收货人真实姓名',
     *      'district_id' => '区县或街道ID',
     *      'mobilephone' => '手机号码',
     *      'address'     => '收货详细地址。除省市区街道外的部分地址信息。',
     * ];
     * -- Example end --
     *
     * @param  array  $data  地址信息。
     * @return array
     */
    public static function getSubmitUserAddressDetail($data)
    {
        if ($data['address_id'] == MallUserAddress::NONE) { // 如果收货地址是新填写的，则验证有效性。
            self::checkConsignee($data['realname']);
            self::checkDistrictId($data['district_id']);
            self::checkMobilephone($data['mobilephone']);
            self::checkAddress($data['address']);
            
            $provinceName = '';
            $cityName     = '';
            $districtName = '';
            $realname     = $data['realname'];
            $address      = $data['address'];
            $mobilephone  = $data['mobilephone'];
        } else {
            $where = [
                'user_id'    => $data['user_id'],
                'address_id' => $data['address_id'],
                'status'     => MallUserAddress::STATUS_NORMAL
            ];
            $AddressModel = new MallUserAddress();
            $addressInfo  = $AddressModel->fetchOne([], $where);
            if (empty($addressInfo)) {
                YCore::exception(STATUS_ERROR, '您选择的收货地址已经失效');
            }
            $DistrictModel = new District();
            $districtInfo  = $DistrictModel->fetchOne([], ['district_id' => $addressInfo['district_id'], 'status' => District::STATUS_NORMAL]);
            if (empty($districtInfo)) {
                YCore::exception(STATUS_ERROR, '您的收货地址的区县已经失效');
            }
            $provinceName = $districtInfo['province_name'];
            $cityName     = $districtInfo['city_name'];
            $districtName = $districtInfo['district_name'];
            $realname      = $addressInfo['realname'];
            $address       = $addressInfo['address'];
            $mobilephone   = $addressInfo['mobilephone'];
        }
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
     * @param  int      $userid         用户ID。
     * @param  string   $realname       真实姓名。
     * @param  string   $mobilephone    手机号码。
     * @param  int      $districtId     地区ID。
     * @param  string   $address        街道详细地址。
     * @return int
     */
    public static function addAddress($userid, $realname, $mobilephone, $districtId, $address)
    {
        self::checkConsignee($realname);
        self::checkMobilephone($mobilephone);
        self::checkDistrictId($districtId);
        self::checkAddress($address);

        $DistrictModel = new District();
        $districtInfo  = $DistrictModel->fetchOne([], ['district_id' => $districtId, 'status' => District::STATUS_NORMAL]);
        if (empty($districtInfo) || $districtInfo['region_type'] < 3) {
            YCore::exception(STATUS_ERROR, '地址有误');
        }
        $addressCount    = self::getUserAddressCount($userid);
        $maxAddressCount = YCore::config('max_user_address_count');
        if ($addressCount >= $maxAddressCount) {
            YCore::exception(6001104, "最多允许创建{$maxAddressCount}个收货地址");
        }

        $data = [
            'realname'     => $realname,
            'mobilephone'  => $mobilephone,
            'district_id'  => $districtId,
            'address'      => $address,
            'status'       => MallUserAddress::STATUS_NORMAL,
            'created_time' => date('Y-m-d H:i:s', time())
        ];

        $UserAddressModel = new MallUserAddress();
        $addressId        = $UserAddressModel->insert($data);
        if ($addressId == 0) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
        return $addressId;
    }

    /**
     * 获取用户收货地址数量。
     *
     * @param  int $userid 用户ID。
     * @return int
     */
    public static function getUserAddressCount($userid)
    {
        $AddressModel = new MallUserAddress();
        $where = [
            'user_id' => $userid,
            'status'  => MallUserAddress::STATUS_NORMAL
        ];
        return $AddressModel->count($where);
    }

    /**
     * 编辑收货地址。
     *
     * @param  int     $userid       用户ID。
     * @param  int     $addressId    收货地址ID。
     * @param  string  $realname     真实姓名。
     * @param  string  $mobilephone  手机号码。
     * @param  int     $districtId   地区ID。
     * @param  string  $address      街道详细地址。
     * @return bool
     */
    public static function editAddress($userid, $addressId, $realname, $mobilephone, $districtId, $address)
    {
        self::checkConsignee($realname);
        self::checkMobilephone($mobilephone);
        self::checkDistrictId($districtId);
        self::checkAddress($address);

        $DistrictModel = new District();
        $districtInfo  = $DistrictModel->fetchOne([], ['district_id' => $districtId, 'status' => District::STATUS_NORMAL]);
        if (empty($districtInfo) || $districtInfo['region_type'] < 3) {
            YCore::exception(STATUS_ERROR, '地址有误');
        }
        self::isExistUserAddress($addressId, $userid);
        $UserAddressModel = new MallUserAddress();
        $data = [
            'realname'      => $realname,
            'district_id'   => $districtId,
            'mobilephone'   => $mobilephone,
            'status'        => MallUserAddress::STATUS_NORMAL,
            'modified_time' => date('Y-m-d H:i:s', time()),
            'address'       => $address
        ];
        $ok = $UserAddressModel->update($data, ['address_id' => $addressId]);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
        return true;
    }

    /**
     * 删除收货地址。
     *
     * @param  int $userid     用户ID。
     * @param  int $addressId  收货地址ID。
     * @return bool
     */
    public static function deleteAddress($userid, $addressId)
    {
        self::isExistUserAddress($addressId, $userid);
        $UserAddressModel = new MallUserAddress();
        $data = [
            'status'        => MallUserAddress::STATUS_DELETED,
            'modified_time' => date('Y-m-d H:i:s', time())
        ];
        $where = [
            'address_id' => $addressId,
            'user_id'    => $userid
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
     * @param  int   $userid  用户ID。
     * @return array
     */
    public static function getAllAddress($userid)
    {
        $where = [
            'user_id' => $userid,
            'status'  => MallUserAddress::STATUS_NORMAL
        ];
        $columns = [
            'address_id',
            'realname',
            'mobilephone',
            'district_id',
            'address'
        ];
        $UserAddressModel = new MallUserAddress();
        $addressList      = $UserAddressModel->fetchAll($columns, $where, 0, 'is_default DESC,address_id ASC');
        foreach ($addressList as $key => $address) {
            $DistrictModel = new District();
            $dis = $DistrictModel->fetchOne([], ['district_id' => $address['district_id']]);
            $address['province_name'] = $dis['province_name'];
            $address['city_name']     = $dis['city_name'];
            $address['district_name'] = $dis['district_name'];
            $address['street_name']   = $dis['street_name'];
            $addressList[$key]        = $address;
        }
        return $addressList;
    }

    /**
     * 设置默认收货地址。
     *
     * @param  int $userid      用户ID。
     * @param  int $addressId   收货地址ID。
     * @return bool
     */
    public static function setDefaultAddress($userid, $addressId)
    {
        self::isExistUserAddress($addressId, $userid);
        $UserAddressModel = new MallUserAddress();
        $UserAddressModel->update(['is_default' => 0], ['user_id' => $userid, 'status' => MallUserAddress::STATUS_NORMAL]);
        $data = [
            'is_default'    => MallUserAddress::DEFAULT_YES,
            'modified_time' => date('Y-m-d H:i:s', time())
        ];
        $where = [
            'address_id' => $addressId,
            'user_id'    => $userid
        ];
        $ok = $UserAddressModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
        return true;
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
        return true;
    }

    /**
     * 检查收货人手机号格式。
     * @param  string $mobilephone 收货人姓名。
     * @return bool
     */
    public static function checkMobilephone($mobilephone)
    {
        $data  = ['mobilephone' => $mobilephone];
        $rules = [
            'mobilephone' => '手机号码|require|mobilephone'
        ];
        Validator::valido($data, $rules);
        return true;
    }

    /**
     * 检查收货人详细地址。
     * @param  string $address 收货人详细地址。
     * @return bool
     */
    public static function checkAddress($address)
    {
        $data  = ['address' => $mobilephone];
        $rules = [
            'address' => '详细地址|require|len:1:50:1'
        ];
        Validator::valido($data, $rules);
        return true;
    }

    /**
     * 检查收货人所在地区。
     * @param  string $districtId 收货人所在地区ID。
     * @return bool
     */
    public static function checkDistrictId($districtId)
    {
        $data  = ['district_id' => $mobilephone];
        $rules = [
            'district_id' => '所在地区|require|number_between:10'
        ];
        Validator::valido($data, $rules);
        $DistrictModel = new District();
        $districtInfo  = $DistrictModel->fetchOne([], [
            'district_id' => $districtId,
            'status'      => District::STATUS_NORMAL
        ]);
        if (empty($districtInfo)) {
            YCore::exception(STATUS_ERROR, '区县ID有误');
        }
        return true;
    }

    /**
     * 用户收货地址是否存在。
     * @param  int      $addressId 收货地址ID。
     * @param  int      $userid    用户ID。
     * @param  string   $errMsg     错误时的提示。
     * @return bool
     */
    public static function isExistUserAddress($addressId, $userid, $errMsg = '')
    {
        $where = [
            'address_id' => $addressId,
            'status'     => MallUserAddress::STATUS_NORMAL,
            'user_id'    => $userid
        ];
        $UserAddressModel = new MallUserAddress();
        $addressInfo      = $UserAddressModel->fetchOne([], $where);
        if (empty($addressInfo)) {
            $errMsg = (strlen($errMsg) > 0) ? $errMsg : '收货地址不存在';
            YCore::exception(6001009, $errMsg);
        }
        return true;
    }
}