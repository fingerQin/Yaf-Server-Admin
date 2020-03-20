<?php
/**
 * 过滤器。
 * -- 1、敏感词过滤。
 * -- 2、特殊值加密。
 * -- 3、特别值脱敏。
 * @author fingerQin
 * @date 2020-03-20
 */

namespace Services\System;

use finger\App;
use finger\Crypt;

class Filter extends \Services\AbstractBase
{
    /**
     * 特殊值加密。
     * 
     * @param  array  $data     待特殊加密的数据。
     * @param  bool   $isForce  是否强制转换。默认情况下只有正式环境会转换。其他环境不转换。
     *
     * @return array 过滤之后的结果。
     */
    public static function specialValueEncrypt(array $data, $isForce = false)
    {
        $encKey = App::getConfig('app.key');
        if ($isForce || App::getConfig('app.env') == ENV_PRO) {
            foreach ($data as $key => $value) {
                if (in_array($key, API_SAFETY_FILTER) && self::isAllowEncryptType($value)) {
                    $data[$key] = Crypt::encrypt($value, $encKey);
                } elseif (is_array($value)) {
                    $data[$key] = self::specialValueEncrypt($value, $isForce);
                }
            }
        }
        return $data;
    }

    /**
     * 检查是否为允许的加密类型。
     * 
     * -- 只允许数值、字符串。
     *
     * @param  mix $value
     *
     * @return bool
     */
    private static function isAllowEncryptType($value)
    {
        if (is_string($value) || is_numeric($value)) {
            return true;
        } else {
            return false;
        }
    }
}