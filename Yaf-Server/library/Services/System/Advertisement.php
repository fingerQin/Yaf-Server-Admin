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
        $count = $adPosDetail['pos_ad_count'] > self::MAX_COUNT ? MAX_COUNT : $adPosDetail['pos_ad_count'];
        $sql = 'SELECT ad_id,ad_name,ad_image_url,ad_url FROM finger_ad '
             . 'WHERE pos_id = :pos_id AND status = :status AND display = :display '
             . 'ORDER BY listorder ASC, ad_id DESC LIMIT :count';
        $params = [
            ':pos_id'  => $adPosDetail['pos_id'],
            ':status'  => Ad::STATUS_YES,
            ':display' => Ad::STATUS_YES,
            ':count'   => $count
        ];
        // iOS 返回的时候，使用高清特制图。
        $data   = [];
        $result = Db::all($sql, $params);
        foreach ($result as $item) {
            if ($platform == self::PLATFORM_IOS) {
                $item['ad_url'] = $item['ad_image_url'];
            }
            unset($item['ad_image_url']);
            $data[] = $item;
        }
        unset($result);
        return $data;
    }
}