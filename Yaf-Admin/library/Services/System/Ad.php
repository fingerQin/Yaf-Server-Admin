<?php
/**
 * 广告管理。
 * @author fingerQin
 * @date 2016-03-30
 */

namespace Services\System;

use finger\Validator;
use finger\Database\Db;
use finger\Utils\YCore;
use finger\Utils\YUrl;
use Models\Ad as AdModel;
use Models\AdPosition;

class Ad extends \Services\AbstractBase
{
    /**
     * 检查广告位置名称格式。
     * @param  string  $posName  广告位置名称。
     * @return void
     */
    public static function checkPosName($posName)
    {
        $data = [
            'pos_name' => $posName
        ];
        $rules = [
            'pos_name' => '广告位置名称|require|len:1:50:1'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查广告位置编码格式。
     * @param  string  $posCode  广告位置编码。
     * @return void
     */
    public static function checkPosCode($posCode)
    {
        $data = [
            'pos_code' => $posCode
        ];
        $rules = [
            'pos_code' => '广告编码|require|len:1:50:1|alpha_dash'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查广告位置数量。
     * @param  int  $posCount  广告数量。
     * @return void
     */
    public static function checkPosCount($posCount)
    {
        $data = [
            'pos_ad_count' => $posCount
        ];
        $rules = [
            'pos_ad_count' => '广告位广告展示数量|require|integer'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查广告名称格式。
     * @param  string  $adNmae  广告名称。
     * @return void
     */
    public static function checkAdName($adNmae)
    {
        $data = [
            'ad_name' => $adNmae
        ];
        $rules = [
            'ad_name' => '广告名称|require|len:1:50:1'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查广告生效开始时间格式。
     * @param  string  $startTime  广告生效开始时间。
     * @return void
     */
    public static function checkAdStartTime($startTime)
    {
        $data = [
            'start_time' => $startTime
        ];
        $rules = [
            'start_time' => '生效时间|require|date:1'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查广告生效结束时间。
     * @param  string $endTime 广告生效结束时间。
     * @return void
     */
    public static function checkAdEndTime($endTime)
    {
        $data = [
            'end_time' => $endTime
        ];
        $rules = [
            'end_time' => '失效时间|require|date:1'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查广告开始时间与结束时间。
     *
     * @param string $startTime 开始时间。
     * @param string $endTime   结束时间。
     * @return void
     */
    public static function checkAdStartTimeOrEndTime($startTime, $endTime)
    {
        self::checkAdStartTime($startTime);
        self::checkAdEndTime($endTime);
        if ($endTime <= $startTime) {
            YCore::exception(STATUS_SERVER_ERROR, '广告生效开始时间必须小于结束时间');
        }
    }

    /**
     * 检查显示终端值是否合法。
     *
     * @param  int  $terminal  终端值。
     *
     * @return void
     */
    public static function checkTerminal($terminal)
    {
        if (empty($terminal)) {
            YCore::exception(STATUS_SERVER_ERROR, '必须选择显示终端');
        }
        foreach ($terminal as $tt) {
            if (!array_key_exists($tt, self::$terminalDict)) {
                YCore::exception(STATUS_SERVER_ERROR, '显示终端值有误');
            }
        }
    }

    /**
     * 检查广告备注。
     * @param  string $remark 广告备注。
     * @return void
     */
    public static function checkAdRemark($remark)
    {
        $data = [
            'remark' => $remark
        ];
        $rules = [
            'remark' => '广告备注|require|len:1:200:1'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查广告图片 URL。
     * @param  string $imageUrl 广告图片链接。
     * @return void
     */
    public static function checkAdImageUrl($imageUrl)
    {
        $data = [
            'ad_image_url' => $imageUrl
        ];
        $rules = [
            'ad_image_url' => '广告图片|require|len:1:100:1'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查广告图片 URL。
     * @param  string $imageUrl 广告图片链接。
     * @return void
     */
    public static function checkAdIpxImageUrl($imageUrl)
    {
        $data = [
            'ad_image_url' => $imageUrl
        ];
        $rules = [
            'ad_image_url' => 'IPhone高清广告图片|require|len:1:100:1'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查广告跳转的 URL
     * @param  string $adUrl 广告跳转 URL。
     * @return void
     */
    public static function checkAdUrl($adUrl)
    {
        $data = [
            'ad_url' => $adUrl
        ];
        $rules = [
            'ad_url' => '广告URL|require|len:1:100:1|url'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查显示状态格式。
     * @param  int  $display 显示状态。
     * @return void
     */
    public static function checkDisplay($display)
    {
        $data = [
            'display' => $display
        ];
        $rules = [
            'display' => '显示状态|require|integer',
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 获取指定位置的广告。
     *
     * @param  string $posCode 广告位置编码。
     * @return array
     */
    public static function getPositionAdList($posCode)
    {
        $where = [
            'pos_code' => $posCode,
            'status'   => AdPosition::STATUS_YES
        ];
        $adPosition     = new AdPosition();
        $positionDetail = $adPosition->fetchOne([], $where);
        if (empty($positionDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '无效的广告位置编码');
        }
        $sql = "SELECT ad_id,ad_name,ad_image_url,ad_url FROM ms_ad WHERE pos_id = :pos_id "
             . "AND start_time <= :start_time AND end_time >= :end_time "
             . "AND status = :status AND display = :display ORDER BY listorder ASC, "
             . "ad_id ASC LIMIT {$positionDetail['pos_ad_count']}";
        $params = [
            ':pos_id'     => $positionDetail['pos_id'],
            ':display'    => AdPosition::STATUS_YES,
            ':status'     => AdPosition::STATUS_YES,
            ':start_time' => date('Y-m-d H:i:s', time()),
            ':end_time'   => date('Y-m-d H:i:s', time())
        ];
        $list = Db::all($sql, $params);
        foreach ($list as $k => $v) {
            $v['ad_image_url'] = YUrl::filePath($v['ad_image_url']);
            $list[$k] = $v;
        }
        return $list;
    }

    /**
     * 获取广告位置列表。
     *
     * @param  string  $keywords  查询关键词。模糊搜索广告名称和广告编码。
     * @param  int     $page      当前页码。
     * @param  int     $count     每页显示条数。
     * @return array
     */
    public static function getAdPostionList($keywords = '', $page = 1, $count = 20)
    {
        $AdPostionModel = new AdPosition();
        return $AdPostionModel->getList($keywords, $page, $count);
    }

    /**
     * 获取广告位置详情。
     *
     * @param  int $posId 广告位置ID。
     * @return array
     */
    public static function getAdPostionDetail($posId)
    {
        $AdPostionModel = new AdPosition();
        $data = $AdPostionModel->fetchOne([], ['pos_id' => $posId, 'status' => AdPosition::STATUS_YES]);
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '广告位置不存在或已经删除');
        }
        return $data;
    }

    /**
     * 添加广告位置。
     *
     * @param  int     $adminId     管理员ID。
     * @param  string  $posName     广告位置名称。
     * @param  string  $posCode     广告位置编码。
     * @param  int     $posAdCount  广告位允许展示的广告数量。
     * @return bool
     */
    public static function addAdPostion($adminId, $posName, $posCode, $posAdCount)
    {
        self::checkPosName($posName);
        self::checkPosCode($posCode);
        self::checkPosCount($posAdCount);
        $AdPosModel  = new AdPosition();
        $adPosDetail = $AdPosModel->fetchOne([], ['pos_code' => $posCode, 'status' => AdPosition::STATUS_YES]);
        if ($adPosDetail) {
            YCore::exception(STATUS_SERVER_ERROR, '广告编码已经存在请更换');
        }
        $data = [
            'pos_name'     => $posName,
            'pos_code'     => $posCode,
            'pos_ad_count' => $posAdCount,
            'status'       => AdPosition::STATUS_YES,
            'c_by'         => $adminId,
            'c_time'       => date('Y-m-d H:i:s', TIMESTAMP)
        ];
        $ok = $AdPosModel->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 编辑广告位置。
     *
     * @param  int     $adminId     管理员ID。
     * @param  int     $posId       广告位ID。
     * @param  string  $posName     广告位置名称。
     * @param  string  $posCode     广告位置编码。
     * @param  int     $posAdCount  广告位允许展示的广告数量。
     * @return void
     */
    public static function editAdPostion($adminId, $posId, $posName, $posCode, $posAdCount)
    {
        self::checkPosName($posName);
        self::checkPosCode($posCode);
        self::checkPosCount($posAdCount);
        self::getAdPostionDetail($posId);

        $AdPosModel = new AdPosition();
        $where = [
            'pos_code' => $posCode,
            'status'   => AdPosition::STATUS_YES
        ];
        $adPosDetail = $AdPosModel->fetchOne([], $where);
        if ($adPosDetail && $adPosDetail['pos_id'] != $posId) {
            YCore::exception(STATUS_SERVER_ERROR, '广告编码已经被占用请更换');
        }
        $data = [
            'pos_name'     => $posName,
            'pos_code'     => $posCode,
            'pos_ad_count' => $posAdCount,
            'u_by'         => $adminId,
            'u_time'       => date('Y-m-d H:i:s', TIMESTAMP)
        ];
        $ok = $AdPosModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除广告位置。
     *
     * @param  int  $adminId  管理员ID。
     * @param  int  $posId    广告位置ID。
     * @return void
     */
    public static function deleteAdPostion($adminId, $posId)
    {
        self::getAdPostionDetail($posId);
        $AdModel = new AdModel();
        $adCount = $AdModel->count(['pos_id' => $posId, 'status' => AdModel::STATUS_YES]);
        if ($adCount > 0) {
            YCore::exception(STATUS_SERVER_ERROR, '请先清空该广告位置下的广告');
        }
        $data = [
            'status' => AdPosition::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', TIMESTAMP)
        ];
        $where = [
            'pos_id' => $posId,
            'status' => AdPosition::STATUS_YES
        ];
        $AdPositionModel = new AdPosition();
        $ok = $AdPositionModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 获取指定位置的广告列表。
     *
     * @param  int     $posId    广告位置ID。
     * @param  string  $adName   广告名称。模糊搜索广告名称。
     * @param  int     $display  显示状态：1是、0否。
     * @param  int     $page     当前页码。
     * @param  int     $count    每页显示记录条数。
     * @return array
     */
    public static function getAdList($posId, $adName = '', $display = -1, $page = 1, $count = 20)
    {
        $AdModel = new AdModel();
        return $AdModel->getList($posId, $adName, $display, $page, $count);
    }

    /**
     * 获取广告详情。
     *
     * @param  int  $adId 广告ID。
     * @return array
     */
    public static function getAdDetail($adId)
    {
        $AdModel = new AdModel();
        $data    = $AdModel->fetchOne([], ['ad_id' => $adId, 'status' => AdPosition::STATUS_YES]);
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '广告不存在或已经删除');
        }
        return $data;
    }

    /**
     * 添加广告。
     *
     * @param  int     $adminId     管理员ID。
     * @param  int     $posId       广告位置ID。
     * @param  string  $adName      广告名称。
     * @param  string  $startTime   广告生效时间。
     * @param  string  $endTime     广告失效时间。
     * @param  int     $display     显示状态：1显示、0隐藏。
     * @param  string  $remark      广告备注。
     * @param  string  $adImageUrl  广告图片。
     * @param  string  $adUrl       广告URL。
     * @param  array   $terminal    显示终端。
     * @param  array   $flag        限制条件 FLAG 位值。
     * @return void
     */
    public static function addAd($adminId, $posId, $adName, $startTime, $endTime, $display, 
        $remark, $adImageUrl, $adIpxImageUrl, $adUrl, $terminal, $flag)
    {
        self::checkAdName($adName);
        self::checkAdRemark($remark);
        self::checkAdImageUrl($adImageUrl);
        self::checkAdIpxImageUrl($adIpxImageUrl);
        self::checkAdUrl($adUrl);
        self::checkDisplay($display);
        self::checkAdStartTimeOrEndTime($startTime, $endTime);

        // 当此广告位置不存在的情况下，此方法会自己抛异常。
        self::getAdPostionDetail($posId);

        $data = [
            'ad_name'          => $adName,
            'start_time'       => $startTime,
            'end_time'         => $endTime,
            'display'          => $display,
            'remark'           => $remark,
            'ad_image_url'     => $adImageUrl,
            'ad_ipx_image_url' => $adIpxImageUrl,
            'ad_url'           => $adUrl,
            'pos_id'           => $posId,
            'status'           => AdModel::STATUS_YES,
            'c_by'             => $adminId,
            'terminal'         => array_sum($terminal),
            'type_flag'        => array_sum($flag),
            'c_time'           => date('Y-m-d H:i:s', TIMESTAMP)
        ];
        $AdModel = new AdModel();
        $ok      = $AdModel->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 编辑广告。
     *
     * @param  int     $adminId     管理员ID。
     * @param  int     $adId        广告ID。
     * @param  string  $adName      广告名称。
     * @param  string  $startTime   广告生效时间。
     * @param  string  $endTime     广告失效时间。
     * @param  int     $display     显示状态：1显示、0隐藏。
     * @param  string  $remark      广告备注。
     * @param  string  $adImageUrl  广告图片。
     * @param  string  $adUrl       广告URL。
     * @param  array   $terminal    显示终端。
     * @param  array   $flag        限制条件 FLAG 位值。
     * @return void
     */
    public static function editAd($adminId, $adId, $adName, $startTime, $endTime, $display, 
        $remark, $adImageUrl, $adIpxImageUrl, $adUrl, $terminal, $flag)
    {
        self::checkAdName($adName);
        self::checkAdRemark($remark);
        self::checkAdImageUrl($adImageUrl);
        self::checkAdIpxImageUrl($adIpxImageUrl);
        self::checkAdUrl($adUrl);
        self::checkDisplay($display);
        self::checkAdStartTimeOrEndTime($startTime, $endTime);
        self::getAdDetail($adId);
        $data = [
            'ad_name'          => $adName,
            'start_time'       => $startTime,
            'end_time'         => $endTime,
            'display'          => $display,
            'remark'           => $remark,
            'ad_image_url'     => $adImageUrl,
            'ad_ipx_image_url' => $adIpxImageUrl,
            'ad_url'           => $adUrl,
            'terminal'         => array_sum($terminal),
            'type_flag'        => array_sum($flag),
            'u_by'             => $adminId,
            'u_time'           => date('Y-m-d H:i:s', TIMESTAMP)
        ];
        $where = [
            'ad_id'  => $adId,
            'status' => AdModel::STATUS_YES
        ];
        $AdModel = new AdModel();
        $ok      = $AdModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除广告。
     *
     * @param  int  $adminId 管理员ID。
     * @param  int  $adId    广告ID。
     * @return void
     */
    public static function deleteAd($adminId, $adId)
    {
        self::getAdDetail($adId);
        $data = [
            'status' => AdModel::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', TIMESTAMP)
        ];
        $where = [
            'ad_id'  => $adId,
            'status' => AdModel::STATUS_YES
        ];
        $AdModel  = new AdModel();
        $ok = $AdModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 广告排序。
     *
     * @param  array $listorders 分类排序数据。[ ['广告ID' => '排序值'], ...... ]
     * @return void
     */
    public static function sortAd($listorders)
    {
        if (empty($listorders)) {
            return;
        }
        foreach ($listorders as $adId => $sortVal) {
            $AdModel = new AdModel();
            $ok = $AdModel->sortAd($adId, $sortVal);
            if (!$ok) {
                return YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
            }
        }
    }

    /**
     * 获取广告字典。
     *
     * @return array
     */
    public static function getAdTerminalDict()
    {
        return AdModel::$terminalDict;
    }

    /**
     * 广告限制条件字典。
     *
     * @return array
     */
    public static function getAdFlagDict()
    {
        return AdModel::$flagDict;
    }
}