<?php
/**
 * 广告业务封装。
 * @author fingerQin
 * @date 2018-08-20
 */

namespace Services\System;

use Utils\YCore;
use Models\AdPosition;
use Models\Ad;
use Models\User;
use finger\Database\Db;

class Advertisement extends \Services\AbstractBase
{
    /**
     * 最大数量。
     * 
     * -- 当广告设置里面的数量大于此值，则返回此值数量的广告。
     */
    const MAX_COUNT = 50;

    /**
     * 获取单个广告。
     *
     * @param  string  $posCode   广告位置。
     * @param  string  $appV      APP 版本号(APP 登录时有用)。
     * @param  int     $userid    用户 ID。
     * @param  int     $platform  平台标识。
     *
     * @return void
     */
    public static function single($posCode, $appV = '', $userid = 0, $platform = 0)
    {
        $list = self::list($posCode, $appV, $userid, $platform);
        return $list ? $list[0] : YCore::getNullObject();
    }

    /**
     * 获取广告列表。
     *
     * @param  string  $posCode   广告位置。
     * @param  string  $appV      APP 版本号(APP 登录时有用)。
     * @param  int     $userid    用户 ID。
     * @param  int     $platform  平台标识。
     *
     * @return void
     */
    public static function list($posCode, $appV = '', $userid = 0, $platform = 0)
    {
        $AdPosModel  = new AdPosition();
        $adPosDetail = $AdPosModel->fetchOne([], ['pos_code' => $posCode, 'status' => AdPosition::STATUS_YES]);
        if (empty($adPosDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '该广告不存在或已经下线');
        }
        $bitVal = self::getUserBitConditionVal($userid);
        $count  = $adPosDetail['pos_ad_count'] > self::MAX_COUNT ? MAX_COUNT : $adPosDetail['pos_ad_count'];
        $sql    = 'SELECT ad_id,ad_name,ad_image_url,ad_ipx_image_url, ad_url FROM finger_ad '
                . 'WHERE pos_id = :pos_id AND status = :status AND display = :display '
                . 'AND type_flag & :bitVal != 0 AND terminal & :terminal != 0 '
                . 'ORDER BY listorder ASC, ad_id DESC LIMIT :count';
        $params = [
            ':pos_id'   => $adPosDetail['pos_id'],
            ':bitVal'   => $bitVal,
            ':terminal' => self::terminalBitVal($platform),
            ':status'   => Ad::STATUS_YES,
            ':display'  => Ad::STATUS_YES,
            ':count'    => $count
        ];
        // iOS 返回的时候，使用高清特制图。
        $data   = [];
        $result = Db::all($sql, $params);
        foreach ($result as $item) {
            if ($platform == self::PLATFORM_IOS) {
                $item['ad_image_url'] = $item['ad_ipx_image_url'];
            }
            unset($item['ad_ipx_image_url']);
            $data[] = $item;
        }
        unset($result);
        return $data;
    }

    /**
     * 获取用户位运算复合值。
     * 
     * -- 定义的有无邀请、有无兑换的 FLAG 未实现。因为该系统暂时未实现这两个功能。如果后续有增加，自行增加即可。
     *
     * @param  int  $userid  用户 ID。
     *
     * @return int
     */
    private static function getUserBitConditionVal($userid)
    {
        // 当一个广告页面有多个广告位时，会反复调用该方法。于是增加一个当次请求内所有的广告调用使用同一个缓存。请求结束缓存也结束。
        $requestCacheKey = "system::ad::bitCondition";
        if (\Yaf_Registry::has($requestCacheKey)) {
            return \Yaf_Registry::get($requestCacheKey);
        }
        // [1] 登录状态。
        $bitVal = 0;
        if ($userid == 0) {
            return Ad::FLAG_LOGIN_NO;
        } else {
            $bitVal += Ad::FLAG_LOGIN_YES;
        }
        $UserModel = new User();
        $userinfo  = $UserModel->fetchOne(['c_time', 'realname', 'identity'], ['userid' => $userid]);
        // [2] 注册 30 天及以上。
        $regDayTsp = strtotime(substr($userinfo['c_time'], 0, 10));
        $nowTime   = time();
        if (($nowTime - $regDayTsp) >= 30 * 24 * 60 * 60) {
            $bitVal += Ad::FLAG_REGISTER_MONTH;
        }
        // [3] 实名。
        if (strlen($userinfo['realname']) == 0) {
            $bitVal += Ad::FLAG_REALNAME_NO;
        } else {
            $bitVal += Ad::FLAG_REALNAME_YES;
        }
        \Yaf_Registry::set($requestCacheKey, $bitVal);
        return $bitVal;
    }
}