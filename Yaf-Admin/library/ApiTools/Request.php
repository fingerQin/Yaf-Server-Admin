<?php
/**
 * 请求服务端 API 接口。
 * 
 * -- API 接口后端已经实现了各种各样的功能。为了不重复开发利用已有的功能完成管理后台的工作。
 * -- 所以，以接口的形式提供这些功能给后台使用。其次，有部分功能不宜放到管理后台。比如，支付。
 * -- 我们通过接口形式提供功能，便于利用 API 服务端强大的技术支撑。
 * 
 * @author fingerQin
 * @date 2018-07-17
 */

namespace ApiTools;

use Utils\YCore;
use Utils\YCache;
use Services\AbstractBase;
use Utils\YLog;

class Request
{
    /**
     * 应用 KEY。
     * 
     * @var string
     */
    protected $apiKey = '';

    /**
     * 应用密钥。
     *
     * @var string
     */
    protected $apiSecret = '';

    /**
     * 管理接口版本号。
     *
     * @var string
     */
    protected $apiVersion = '';

    /**
     * 构造方法。
     * 
     * @return void
     */
    public function __construct()
    {
        $this->apiKey     = YCore::appconfig('api.admin.key');
        $this->apiSecret  = YCore::appconfig('api.admin.secret');
        $this->apiVersion = YCore::appconfig('api.admin.version');
    }

    /**
     * 发送请求。
     * 
     * @param  array  $params  请求参数。
     * 
     * @return array 失败将抛异常。成功返回数组。
     */
    public function send($params)
    {
        $params = $this->mergeParams($params);
        $apiUrl = YCore::appconfig('domain.api');
        $json   = json_encode($params, JSON_UNESCAPED_UNICODE);
        $sign   = $this->encrypt($json);
        $apiParams = [
            'data' => $json,
            'sign' => $sign
        ];
        $apiUrl = rtrim($apiUrl, '/');
        list($response, $curlErrno, $curlError) = $this->post($apiUrl, $apiParams);
        if ($curlErrno != 0) {
            $response = [
                'code' => STATUS_SERVER_ERROR,
                'msg'  => '接口请求失败'
            ];
        }
        $logData = [
            'url'        => $apiUrl,
            'request'    => $params,
            'response'   => $response,
            'oriJson'    => $json,
            'sign'       => $sign,
            'curl_errno' => $curlErrno,
            'curl_error' => $curlError,
        ];
        YLog::log($logData, 'apis', 'log', YLog::LOG_TYPE_API);
        return $response;
    }

    /**
     * 合并 API 接口固定参数。
     * 
     * @param  array  $params  任意请求参数。
     * 
     * @return array
     */
    protected function mergeParams($params)
    {
        $defaultParams = [
            'v'          => $this->apiVersion,
            'appid'      => $this->apiKey,
            'platform'   => AbstractBase::PLATFORM_ADMIN,
            'request_no' => $this->createRequestSn(),
            'timestamp'  => time()
        ];
        return array_merge($defaultParams, $params);
    }

    /**
     * 创建一个请求号。
     * 
     * @return string
     */
    protected function createRequestSn()
    {
        $redis    = YCache::getRedisClient();
        $time     = time();
        $YmdHi    = date('YmdHi', $time);
        $cacheKey = 'Yaf-Admin-request-api-cache-key-' . $YmdHi;
        $int      = $redis->incr($cacheKey);
        if ($int == 1) {
            $expireTime = $time + 61;
            $redis->expireAt($cacheKey, $expireTime);
        }
        $logNum = str_pad($int, 10, 0, STR_PAD_LEFT);
        return "Req-{$YmdHi}{$logNum}";
    }

    /**
     * 生成签名。
     * 
     * @param  array  $json  接口请求参数 JSON 串。
     * @return string
     */
    private function encrypt($json)
    {
        return strtoupper(md5($json . $this->apiSecret));
    }

    /**
     * 向接口发送 POST 请求。
     * 
     * @param  string  $url   接口地址。
     * @param  array   $data  请求参数。
     * @return array
     */
    private function post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        // 设置该请求是一个POST请求。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        if (FALSE == $response) {
            $result = [];
        } else {
            $result = json_decode($response, true);
        }
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        if ($curlErrno != 0) {
            YLog::log([
                'url'        => $url, 
                'curl_error' => $curlErrno, 
                'curl_errno' => $curlError], 'curl', 'error');
        }
        curl_close($ch);
        return [$result, $curlErrno, $curlError];
    }
}
