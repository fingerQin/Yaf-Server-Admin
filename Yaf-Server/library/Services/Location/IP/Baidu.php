<?php
/**
 * 百度 IP 定位。
 * --1) 文档地址：http://lbsyun.baidu.com/index.php?title=webapi/ip-api
 * @author fingerQin
 * @date 2018-09-12
 */

namespace Services\Location\IP;

use finger\Utils\YCore;
use finger\Utils\YLog;
use finger\Utils\YCache;
use Models\District;

class Baidu
{
    /**
     * 获取定位数据。
     * 
     * -- 缓存 30 分钟，避免接口请求频繁以及响应时间的问题。
     *
     * @param  string  $ip  IP 地址。
     *
     * @return array
     */
    public function get($ip)
    {
        $cacheKey  = "loc-ip:{$ip}";
        $locResult = YCache::get($cacheKey);
        if ($locResult !== FALSE) {
            return $locResult;
        } else {
            $result = $this->request($ip);
            if (empty($result)) {
                YCore::exception(STATUS_SERVER_ERROR, '定位失败');
            }
            if ($result['status'] != 0) {
                YLog::log(['position' => 'baidu-ip', 'ip' => $ip], 'location', 'log');
                YCore::exception(STATUS_SERVER_ERROR, '定位失败');
            }
            // @todo 后续将所有的地区数据放入缓存当中。加速定位的速度。
            $cityName      = $result['content']['address_detail']['city'];
            $DistrictModel = new District();
            $district      = $DistrictModel->fetchOne([], [
                'city_name'   => $cityName, 
                'region_type' => District::REGION_TYPE_CITY
            ]);
            if (empty($district)) {
                YCore::exception(STATUS_SERVER_ERROR, '定位失败');
            }
            $locResult = [
                'province_name' => $district['province_name'],
                'province_code' => $district['province_code'],
                'city_name'     => $district['city_name'],
                'city_code'     => $district['city_code']
            ];
            YCache::set($cacheKey, $locResult, 1800);
            return $locResult;
        }
    }

    /**
     * 向接口发送 POST 请求。
     * 
     * @param  string  $ip  IP 地址。
     * 
     * @return array
     */
    private function request($ip)
    {
        $key = YCore::appconfig('location.ip.key');
        $url = "https://api.map.baidu.com/location/ip?ip={$ip}&ak={$key}";
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $response = curl_exec($ch);
        if (FALSE == $response) {
            $result = [];
        } else {
            $result = json_decode($response, true);
        }
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        if ($curlErrno != 0) {
            $log = [
                'curl_error' => $curlErrno,
                'curl_errno' => $curlError,
                'ip'         => $ip
            ];
            YLog::log($log, 'curl', 'baidu-ip');
        }
        curl_close($ch);
        return $result;
    }
}