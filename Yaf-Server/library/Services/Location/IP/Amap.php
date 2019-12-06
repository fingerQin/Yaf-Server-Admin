<?php
/**
 * 高德 IP 定位。
 * --1) 文档地址：https://lbs.amap.com/api/webservice/guide/api/ipconfig
 * @author fingerQin
 * @date 2018-09-12
 */

namespace Services\Location\IP;

use finger\App;
use finger\Cache;
use finger\Core;
use Models\District;

class Amap
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
        $locResult = Cache::get($cacheKey);
        if ($locResult !== FALSE) {
            return $locResult;
        } else {
            $result = $this->request($ip);
            if (empty($result)) {
                Core::exception(STATUS_SERVER_ERROR, '定位失败');
            }
            if ($result['resultcode'] != 200) {
                App::log(['position' => 'amap-ip', 'ip' => $ip], 'location', 'log');
                Core::exception(STATUS_SERVER_ERROR, '定位失败');
            }
            // @todo 后续将所有的地区数据放入缓存当中。加速定位的速度。
            $parseResult = $this->parseArea($result['result']['area']);
            if (empty($parseResult)) {
                App::log(['position' => 'amap-ip', 'ip' => $ip, 'result' => $result], 'location', 'log');
                Core::exception(STATUS_SERVER_ERROR, '定位失败');
            }
            $DistrictModel = new District();
            $district      = $DistrictModel->fetchOne([], [
                'city_name'   => $parseResult['city_name'], 
                'region_type' => District::REGION_TYPE_CITY
            ]);
            if (empty($district)) {
                Core::exception(STATUS_SERVER_ERROR, '定位失败');
            }
            $locResult = [
                'province_name' => $district['province_name'],
                'province_code' => $district['province_code'],
                'city_name'     => $district['city_name'],
                'city_code'     => $district['city_code']
            ];
            Cache::set($cacheKey, $locResult, 1800);
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
        $key = App::getConfig('location.ip.key');
        $url = "http://apis.juhe.cn/ip/ip2addr?ip={$ip}&key={$key}";
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
            App::log($log, 'curl', 'juhe-ip');
        }
        curl_close($ch);
        return $result;
    }
}