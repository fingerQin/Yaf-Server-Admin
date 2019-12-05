<?php
/**
 * API 请求解密与验签。
 * 
 * @author fingerQin
 * @date 2018-09-25
 */

namespace Apis;

use finger\App;
use finger\Utils\YCore;

class DecodeAdapter
{
    /**
     * 接口数据解密类型。
     */
    const DECODE_JSON = 'json'; // JSON 格式数据。
    const DECODE_FORM = 'form'; // 表单数据格式。

    /**
     * 解析请求数据。
     * 
     * @param  array  $params  请求参数。
     *
     * @return array
     */
    public static function parse($params)
    {
        $apiDecodeDriver = App::getConfig('api.decode.driver');
        switch ($apiDecodeDriver) {
            case self::DECODE_FORM:
                return FormDecodeDriver::parse($params);
                break;
            case self::DECODE_JSON:
                return JsonDecodeDriver::parse($params);
                break;
            default:
                YCore::exception(STATUS_SERVER_ERROR, 'API 参数解密驱动配置有误');
                break;
        }
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
        $apiDecodeDriver = App::getConfig('api.decode.driver');
        switch ($apiDecodeDriver) {
            case self::DECODE_FORM:
                return FormDecodeDriver::checkSign($params, $apiSecret);
                break;
            case self::DECODE_JSON:
                return JsonDecodeDriver::checkSign($params, $apiSecret);
                break;
            default:
                YCore::exception(STATUS_SERVER_ERROR, 'API 参数解密驱动配置有误');
                break;
        }
    }
}