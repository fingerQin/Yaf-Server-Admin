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
     * 获取单个广告。
     *
     * @param  string  $posCode  广告位置。
     * @param  string  $appV     APP 版本号(APP 登录时有用)。
     * @param  int     $userid   用户 ID。
     *
     * @return void
     */
    public static function single($posCode, $appV = '', $userid = 0)
    {
        $list = self::list($posCode);
        return $list ? $list[0] : YCore::getNullObject();
    }

    /**
     * 获取广告列表。
     *
     * @param  string  $posCode  广告位置。
     * @param  string  $appV     APP 版本号(APP 登录时有用)。
     * @param  int     $userid   用户 ID。
     *
     * @return void
     */
    public static function list($posCode, $appV = '', $userid = 0)
    {
        $AdPosModel  = new AdPosition();
        $adPosDetail = $AdPosModel->fetchOne([], ['pos_code' => $posCode, 'status' => AdPosition::STATUS_YES]);
        if (empty($adPosDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '该广告不存在或已经下线');
        }
        $sql = 'SELECT ad_id,ad_name,ad_image_url,ad_url FROM finger_ad '
             . 'WHERE pos_id = :pos_id AND status = :status AND display = :display '
             . 'ORDER BY listorder ASC, ad_id DESC LIMIT :count';
        $params = [
            ':pos_id'  => $adPosDetail['pos_id'],
            ':status'  => Ad::STATUS_YES,
            ':display' => Ad::STATUS_YES,
            ':count'   => $adPosDetail['pos_ad_count']
        ];
        return Db::all($sql, $params);
    }
}