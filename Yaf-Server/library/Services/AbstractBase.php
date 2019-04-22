<?php
/**
 * 业务基类。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Services;

use Utils\YCore;

abstract class AbstractBase
{
    /**
     * 平台类型常量。
     */
    const PLATFORM_IOS     = 1; // IOS。
    const PLATFORM_ANDROID = 2; // Android。
    const PLATFORM_H5      = 3; // 手机触屏版。
    const PLATFORM_WEB     = 4; // PC WEB。
    const PLATFORM_ADMIN   = 5; // 管理后台。

    /**
     * 平台对应位值常量。
     */
    const PLATFORM_BIT_APP = 1; // APP。
    const PLATFORM_BIT_M   = 2; // M 站。
    const PLATFORM_BIT_PC  = 4; // PC。

    /**
     * 是否客户端访问。
     *
     * @param  int  $platform  平台标识。1-IOS|2-Android|4-h5|5-web。
     *
     * @return bool true-客户端访问、false-非客户端访问。
     */
    protected static function isAppCall($platform)
    {
        if (in_array($platform, [self::PLATFORM_IOS, self::PLATFORM_ANDROID])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 终端(平台)对应的位运算值。
     *
     * @param  int  $platform  平台标识。
     *
     * @return int
     */
    public static function terminalBitVal($platform)
    {
        switch ($platform) {
            case self::PLATFORM_IOS:
            case self::PLATFORM_ANDROID:
                return self::PLATFORM_BIT_APP;
            case self::PLATFORM_H5:
                return self::PLATFORM_BIT_M;
            case self::PLATFORM_WEB:
                return self::PLATFORM_BIT_PC;
            default:
                YCore::exception(STATUS_SERVER_ERROR, '所属平台值有误');
        }
    }

    /**
     * 计算是否有下一页。
     *
     * @param  int  $total  总条数。
     * @param  int  $page   当前页。
     * @param  int  $count  每页显示多少条。
     * @return bool
     */
    public static function isHasNextPage($total, $page, $count)
    {
        if (!$total || !$count) {
            return false;
        }
        $totalPage = ceil($total / $count);
        if (!$totalPage) {
            return false;
        }
        if ($totalPage <= $page) {
            return false;
        }
        return true;
    }

    /**
     * 计算并返回每页的offset。
     *
     * @param  int  $page   页码。
     * @param  int  $count  每页显示记录条数。
     * @return int
     */
    public static function getPaginationOffset($page, $count)
    {
        $count = ($count <= 0) ? 10 : $count;
        $page  = ($page <= 0) ? 1 : $page;
        return ($page == 1) ? 0 : (($page - 1) * $count);
    }
}