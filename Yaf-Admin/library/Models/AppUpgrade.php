<?php
/**
 * APP客户端升级表 Model。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Models;

class AppUpgrade extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_app_upgrade';

    protected $primaryKey = 'id';

    const UPGRADE_WAY_NO     = 0;   // 不升级。
    const UPGRADE_WAY_ADVISE = 1;   // 建议升级。
    const UPGRADE_WAY_FORCE  = 2;   // 强制升级。
    const UPGRADE_WAY_CLOSE  = 3;   // 应用关闭。

    /**
     * APP 客户端类型。
     */
    const APP_TYPE_IOS     = 1; // IOS 客户端。
    const APP_TYPE_ANDROID = 2; // Android 客户端。
}