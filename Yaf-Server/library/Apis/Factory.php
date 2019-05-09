<?php
/**
 * API 请求调用全部在此工厂类中。
 *
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Apis;

use Utils\YCore;
use Utils\YCache;
use Utils\YInput;
use Utils\YLog;
use Models\ApiAuth;

class Factory
{
    /**
     * 根据接口名称返回接口对象。
     * 
     * -- 1、接口名称转类名称规则：user.login = UserLoginApi
     * -- 2、当method参数为空的时候，要抛出异常给调用的人捕获处理。
     *
     * @param  array  $apiData 请求来的所有参数。
     * @throws Exception
     * @return Api
     */
    public static function factory(&$apiData)
    {
        // [1]
        $reqParams = DecodeAdapter::parse($apiData);
        self::writeRequestLog($apiData, $reqParams);

        // [2]
        if (!isset($reqParams['method']) || strlen($reqParams['method']) === 0) {
            YCore::exception(STATUS_ERROR, 'method does not exist');
        }
        if (!isset($reqParams['v']) || strlen($reqParams['v']) === 0) {
            YCore::exception(STATUS_ERROR, 'version number is wrong');
        }
        if (!isset($reqParams['appid']) || strlen($reqParams['appid']) === 0) {
            YCore::exception(STATUS_ERROR, 'appid parameters cannot be empty');
        }
        if (!isset($reqParams['timestamp']) || strlen($reqParams['timestamp']) === 0) {
            YCore::exception(STATUS_ERROR, 'timestamp parameters cannot be empty');
        }

        // [3] 将 method 参数转换为实际的接口类名称。
        $apiName   = $reqParams['method'];
        $params    = explode('.', $apiName);
        $classname = '';
        foreach ($params as $param) {
            $classname .= ucfirst($param);
        }
        // 1.0.0 => v1_0_0
        $version = str_replace('.', '', $reqParams['v']);

        // [4]
        $apiDir = self::apiDir($reqParams['method']);
        if (strlen($apiDir) > 0) {
            $apiDir = "{$apiDir}\\";
        }

        // [5] 映射接口类。
        $apiDetail = self::getApiDetail($reqParams['appid']);
        $classname = "Apis\\{$apiDetail['api_type']}\\v{$version}\\{$apiDir}{$classname}Api";

        if (strlen($apiName) && class_exists($classname)) {
            return new $classname($reqParams, $apiDetail['api_type'], $apiDetail['api_key'], $apiDetail['api_secret']);
        } else {
            YCore::exception(STATUS_SERVER_ERROR, '您的APP太旧请升级!');
        }
    }

    /**
     * 记录请求日志。
     *
     * @param  array  $oriReqData  原始的请求数据。
     * @param  array  $params      格式化指定解密方式之后的请求数据。
     *
     * @return void
     */
    private static function writeRequestLog(&$oriReqData, &$params)
    {
        $token  = YInput::getString($params, 'token', '');
        $reqLog = [
            '_userid'   => \Services\User\Auth::getTokenUserId($token),
            '_ip'       => YCore::ip(),
            '_datetime' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
        ];
        $reqLog = array_merge($reqLog, $params);
        \Utils\YLog::writeApiRequestLog($reqLog);
    }

    /**
     * 获取接口配置详情。
     *
     * @param  int  $appid  应用 ID。
     * @return array
     */
    private static function getApiDetail($appid)
    {
        $ApiAuthModel = new ApiAuth();
        $columns      = ['api_type', 'api_key', 'api_secret'];
        $where        = ['api_key' => $appid, 'api_status' => ApiAuth::STATUS_YES];
        $detail       = $ApiAuthModel->fetchOne($columns, $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, 'Bad Request');
        }
        return $detail;
    }

    /**
     * 接口名称转目录。
     * 
     * -- 取第一个单词为目录。
     * -- user.login -> User
     * -- user.address.list -> User
     *
     * @param  string $method  方法名称。
     * @return string
     */
    private static function apiDir($method)
    {
        $slice = explode('.', $method);
        $sliceCount = count($slice);
        if ($sliceCount == 1) {
            return '';
        }
        return ucfirst($slice[0]);
    }
}