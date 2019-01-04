<?php
/**
 * Form 表单解密驱动。
 * @author fingerQin
 * @date 2018-09-25
 */

namespace Apis;

use Utils\YCore;

class FormDecodeDriver
{
   /**
     * 解析请求数据。
     * 
     * @param  array  $params  请求参数。
     *
     * @return array
     */
    public static function parse($params)
    {
        return $params['post'];
    }

    /**
     * 验证码请求签名。
     * 
     * @param  array  $params     请求参数。
     * @param  string $apiSecret  API 密钥。
     *
     * @return bool
     */
    public static function checkSign($params, $apiSecret)
    {
        $sign = $params['sign'];
        unset($sign);
        ksort($params);
        $str = '';
        foreach ($params as $key => $value) {
            if (is_string($value)) {
                $str .= "{$key}{$value}"; // 提交的数组不计入签名运算。
            }
        }
        $str    = $str . $apiSecret;
        $okSign = strtoupper(md5($str));
        if (YCore::appconfig('app.env') != ENV_DEV) {
            if (strlen($sign) === 0 || $sign != $okSign) {
                YCore::exception(STATUS_SERVER_ERROR, 'API signature error');
            }
        }
    }
}