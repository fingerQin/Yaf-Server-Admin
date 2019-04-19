<?php
/**
 * APP 应用管理。
 * @author fingerQin
 * @date 2018-07-10
 */

namespace Services\System;

use Utils\YCore;
use finger\Validator;
use finger\Database\Db;
use Models\AppUpgrade;

class App extends \Services\AbstractBase
{
    /**
     * APP 客户端类型。
     * 
     * @var array
     */
    private static $appTypeDict = [
        AppUpgrade::APP_TYPE_IOS,
        AppUpgrade::APP_TYPE_ANDROID
    ];

    /**
     * APP 客户端类型对应中文标签。
     *
     * @var array
     */
    public static $appTypeLabes = [
        AppUpgrade::APP_TYPE_IOS     => 'iOS',
        AppUpgrade::APP_TYPE_ANDROID => 'Android'
    ];

    /**
     * APP 客户端升级方式。
     * 
     * -- 注:这里指的是当前APP 客户端升级到最新版APP客户端的升级方式。
     * 
     * @var array
     */
    private static $upgradeWayDict = [
        AppUpgrade::UPGRADE_WAY_NO,
        AppUpgrade::UPGRADE_WAY_ADVISE,
        AppUpgrade::UPGRADE_WAY_CLOSE,
        AppUpgrade::UPGRADE_WAY_FORCE
    ];

    /**
     * APP 客户端升级方式对应中文标签。
     *
     * @var array
     */
    public static $upgradeWayLabels = [
        AppUpgrade::UPGRADE_WAY_NO     => '不升级',
        AppUpgrade::UPGRADE_WAY_ADVISE => '建议升级',
        AppUpgrade::UPGRADE_WAY_FORCE  => '强制升级',
        AppUpgrade::UPGRADE_WAY_CLOSE  => '应用关闭'
    ];

    /**
     * 安卓发布渠道字典。
     * 
     * @var array
     */
    public static $AndroidChannelDict = [
        'yingyongbao',
        '163',
        'wandou',
        'xiaomi',
        'baidu',
        'huawei',
        '360',
        'yingyonghui',
        'youyi',
        'mumayi',
        'ppandroid',
        'lenovo',
        'oppo',
        'meizu',
        'jinli', 
        'sogou',
        'shop',
        'nduo',
        'jifeng',
        'anzhi',
        'sanxing',
        'uc',
        'vivo',
        'opera',
        'yyb',
        'assist91'
    ];

    /**
     * 获取应用详情。
     * 
     * @param  int  $id  应用 ID。
     * @return array
     */
    public static function detail($id)
    {
        $where = [
            'id'         => $id,
            'app_status' => AppUpgrade::STATUS_YES
        ];
        $columns = ['id', 'app_type', 'app_title', 'app_v', 'app_desc', 'url', 'upgrade_way'];
        $model   = new AppUpgrade();
        $appinfo = $model->fetchOne([], $where);
        if (empty($appinfo)) {
            YCore::exception(1000000, 'APP应用不存在或已经删除');
        }
        return $appinfo;
    }

    /**
     * 编辑 APP 应用。
     * 
     * @param  int      $adminid       管理员ID。
     * @param  int      $id            应用主键ID。
     * @param  int      $appType       APP 应用类型：1-Android|2-IOS。
     * @param  string   $appTitle      APP 应用升级标题。
     * @param  string   $appV          APP 版本号。
     * @param  string   $appDesc       APP 应用升级描述。
     * @param  string   $url           APP 应用下载地址。
     * @param  int      $upgradeWay    APP 升级方式。0-不升级|1-建议升级|2-强制升级|3-应用关闭
     * @param  int      $dialogRepeat  升级弹窗。0 - 只弹一次，1 - 每次都弹。
     * @param  string   $channel       APP 渠道。
     * @return void
     */
    public static function edit($adminid, $id, $appType, $appTitle, $appV, $appDesc, $url, $upgradeWay, $dialogRepeat, $channel)
    {
        // [1] 验证
        $data = [
            'app_type'      => $appType,
            'app_title'     => $appTitle,
            'app_v'         => $appV,
            'app_desc'      => $appDesc,
            'url'           => $url,
            'upgrade_way'   => $upgradeWay,
            'dialog_repeat' => $dialogRepeat,
            'channel'       => $channel
        ];
        $rules = [
            'app_type'      => 'APP应用类型|require|integer',
            'app_title'     => 'APP应用升级标题|require|len:1:20:1',
            'app_v'         => 'APP版本|require|len:1:10:0',
            'app_desc'      => 'APP应用升级描述|require|len:1:255:1',
            'url'           => 'APP应用下载地址|url',
            'upgrade_way'   => 'APP应用升级方式|require|integer',
            'dialog_repeat' => '升级弹窗|require|integer|number_between:0:1',
            'channel'       => 'Android渠道|len:1:20:0'
        ];
        Validator::valido($data, $rules); // 验证不通过会抛异常。
        self::checkAppUpgradeWay($upgradeWay);
        self::checkAppType($appType);
        // [2] 记录存在与否。
        $where = [
            'id'         => $id,
            'app_status' => AppUpgrade::STATUS_YES
        ];
        $model   = new AppUpgrade();
        $appinfo = $model->fetchOne([], $where);
        if (empty($appinfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '记录不存在或已经删除');
        }
        // [3] 更新。
        $data['u_time'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $ok = $model->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 添加 APP 应用。
     * 
     * @param  int      $adminid       管理员ID。
     * @param  int      $appType       APP 应用类型：1-Android|2-IOS。
     * @param  string   $appTitle      APP 应用升级标题。
     * @param  string   $appV          APP 版本号。
     * @param  string   $appDesc       APP 应用升级描述。
     * @param  string   $url           APP 应用下载地址。
     * @param  int      $upgradeWay    APP 升级方式。0-不升级|1-建议升级|2-强制升级|3-应用关闭
     * @param  int      $dialogRepeat  升级弹窗。0 - 只弹一次，1 - 每次都弹。
     * @param  string   $channel       APP 渠道。
     * @return void
     */
    public static function add($adminId, $appType, $appTitle, $appV, $appDesc, $url, $upgradeWay, $dialogRepeat, $channel)
    {
        // [1] 验证
        $data = [
            'app_type'      => $appType,
            'app_title'     => $appTitle,
            'app_v'         => $appV,
            'app_desc'      => $appDesc,
            'url'           => $url,
            'upgrade_way'   => $upgradeWay,
            'dialog_repeat' => $dialogRepeat,
            'channel'       => $channel
        ];
        $rules = [
            'app_type'      => 'APP应用类型|require|integer',
            'app_title'     => 'APP应用升级标题|require|len:1:20:1',
            'app_v'         => 'APP版本|require|len:1:10:0',
            'app_desc'      => 'APP应用升级描述|require|len:1:255:1',
            'url'           => 'APP应用下载地址|url',
            'upgrade_way'   => 'APP应用升级方式|require|integer',
            'dialog_repeat' => '升级弹窗|require|integer|number_between:0:1',
            'channel'       => 'Android渠道|len:1:20:0'
        ];
        Validator::valido($data, $rules); // 验证不通过会抛异常。
        self::checkAppUpgradeWay($upgradeWay);
        self::checkAppType($appType);
        $data['app_status'] = AppUpgrade::STATUS_YES;
        $data['c_by']       = $adminId;
        $model              = new AppUpgrade();
        $aupgradeid         = $model->insert($data);
        if ($aupgradeid == 0) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除应用。
     * 
     * @param  int  $adminId  管理员ID。
     * @param  int  $id       应用ID。
     * @return void
     */
    public static function delete($adminId, $id)
    {
        $where = [
            'id'         => $id,
            'app_status' => AppUpgrade::STATUS_YES
        ];
        $appModel = new AppUpgrade();
        $appinfo  = $appModel->fetchOne([], $where);
        if (empty($appinfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '应用不存在或已经删除');
        }
        $updata = [
            'app_status' => AppUpgrade::STATUS_DELETED,
            'u_time'     => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
        ];
        $ok = $appModel->update($updata, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '删除失败');
        }
    }

    /**
     * 获取 APP 应用列表。
     * 
     * @param  int     $appType  APP 类型。1 - Android、2 - IOS。
     * @param  string  $channel  Android 渠道。
     * @param  string  $appV     APP 版本号。
     * @param  int     $page     当前页码。
     * @param  int     $count    每页显示条数。
     * @return array
     */
    public static function list($appType = -1, $channel = '', $appV = '', $page = 1, $count = 20)
    {
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' id, app_type, app_title, app_v, app_desc, url, upgrade_way, dialog_repeat, channel, c_time, u_time';
        $where   = ' WHERE app_status = :app_status ';
        $params  = [
            ':app_status' => AppUpgrade::STATUS_YES
        ];
        if ($appType != -1) {
            $where .= ' AND app_type = :app_type ';
            $params[':app_type'] = $appType;
        }
        if (strlen($channel) > 0) {
            $where .= ' AND channel = :channel ';
            $params[':channel'] = $channel;
        }
        if (strlen($appV) > 0) {
            $where .= ' AND app_v = :app_v ';
            $params[':app_v'] = $appV;
        }
        $orderBy   = ' ORDER BY id DESC ';
        $sql       = "SELECT COUNT(1) AS count FROM finger_app_upgrade {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} FROM finger_app_upgrade {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $list[$key]['upgrade_way_txt']   = self::upgradeWayTranslate($item['upgrade_way']);
            $list[$key]['app_type_txt']      = self::appTypeTranslate($item['app_type']);
            $list[$key]['dialog_repeat_txt'] = $item['dialog_repeat'] ? '每次都弹' : '只弹一次';
        }
        $result = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 升级方式翻译为对应的中文含义。
     * 
     * @param  int  $upgradeWay  升级方式。
     * @return string
     */
    private static function upgradeWayTranslate($upgradeWay)
    {
        return self::$upgradeWayLabels[$upgradeWay];
    }

    /**
     * APP 客户端类型翻译为对应的中文含义。
     * 
     * @param  int  $appType  APP 客户端类型。
     * @return string
     */
    private static function appTypeTranslate($appType)
    {
        return self::$appTypeLabes[$appType];
    }

    /**
     * 验证 APP 客户端类型。
     * 
     * @param  int  $appType  APP 客户端类型。
     * @return void
     */
    private static function checkAppType($appType)
    {
        if (!in_array($appType, self::$appTypeDict)) {
            YCore::exception(STATUS_SERVER_ERROR, 'APP 客户端类型有误!');
        }
    }

    /**
     * 验证 APP 客户端升级方式是否合法。
     * 
     * @param  int  $upgradeWay  升级方式。
     * @return void
     */
    private static function checkAppUpgradeWay($upgradeWay)
    {
        if (!in_array($upgradeWay, self::$upgradeWayDict)) {
            YCore::exception(STATUS_SERVER_ERROR, 'APP 客户端升级方式有误!');
        }
    }
}
