<?php
/**
 * 高德 GPS(经纬度) 定位。
 * --1) 文档地址：https://lbs.amap.com/api/webservice/guide/api/georegeo
 * @author fingerQin
 * @date 2018-09-12
 */

namespace Services\Location\GPS;

use finger\App;
use finger\Utils\YCore;
use finger\Utils\YLog;
use finger\Utils\YCache;
use Models\District;

class Amap
{
    /**
     * 获取定位数据。
     *
     * -- 定位结果，保留 30 分钟。
     * 
     * @param  float  $long  经度。
     * @param  float  $lat   纬度。
     *
     * @return array
     */
    public function get($long, $lat)
    {
        $cacheKey  = "loc-gps:{$long},{$lat}";
        $locResult = YCache::get($cacheKey);
        if ($locResult !== FALSE) {
            return $locResult;
        } else {
            $result = $this->request($long, $lat);
            if (empty($result)) {
                YCore::exception(STATUS_SERVER_ERROR, '定位失败');
            }
            if ($result['status'] != 1) {
                YLog::log(['position' => 'amap-gps', 'long' => $long, 'lat' => $lat], 'location', 'log');
                YCore::exception(STATUS_SERVER_ERROR, '定位失败');
            }
            // @todo 后续将所有的地区数据放入缓存当中。加速定位的速度。
            $cityName = $result['regeocode']['addressComponent']['city'];
            if (empty($cityName)) {
                $log = [
                    'position' => 'amap-gps', 
                    'long'     => $long, 
                    'lat'      => $lat, 
                    'address'  => $result['regeocode']['addressComponent']
                ];
                YLog::log($log, 'location', 'log');
                YCore::exception(STATUS_SERVER_ERROR, '定位失败');
            }
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
     * @param  float  $long  经度。
     * @param  float  $lat   纬度。
     * 
     * @return array
     */
    private function request($long, $lat)
    {
        $key = App::getConfig('location.gps.key');
        $url = "https://restapi.amap.com/v3/geocode/regeo?output=json&location={$long},{$lat}&key={$key}&extensions=base";
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
                'long'       => $long, 
                'lat'        => $lat
            ];
            YLog::log($log, 'curl', 'amap-gps');
        }
        curl_close($ch);
        return $result;
    }
}