<?php
/**
 * 位置定位。
 * @author fingerQin
 * @date 2018-09-12
 */

namespace Services\Location;

use finger\App;
use finger\Core;

class Location extends \Services\AbstractBase
{
    /**
     * GPS 定位驱动字典枚举。
     */
    const GPS_DRIVER_BAIDU = 'GPS-baidu'; // 百度。
    const GPS_DRIVER_AMAP  = 'GPS-amap';  // 高德。

    /**
     * IP 定位驱动枚举。
     */
    const IP_DRIVER_BAIDU = 'IP-baidu'; // 百度。
    const IP_DRIVER_AMAP  = 'IP-amap';  // 高德。

    /**
     * IP 定位。
     *
     * @param  string  $ip  IP 地址。
     * @return array
     */
    public function ip($ip)
    {
        $ipDriver = App::getConfig('location.ip.driver');
        $ipDriver = "IP-" . strtolower($ipDriver);
        switch ($ipDriver) {
            case self::IP_DRIVER_BAIDU:
                $BaiduIP = new \Services\Location\IP\Baidu();
                $result  = $BaiduIP->get($ip);
                break;
            case self::IP_DRIVER_AMAP:
                $AmapIP = new \Services\Location\IP\Amap();
                $result = $AmapIP->get($ip);
                break;
            default:
                Core::exception(STATUS_SERVER_ERROR, '定位驱动设置错误');
                break;
        }
        return $result;
    }

    /**
     * GPS 经纬度定位。
     *
     * @param  float  $long  经度。
     * @param  float  $lat   纬度。
     * @return array
     */
    public function gps($long, $lat)
    {
        $GPSDriver = App::getConfig('location.gps.driver');
        $GPSDriver = "GPS-" . strtolower($GPSDriver);
        switch ($GPSDriver) {
            case self::GPS_DRIVER_BAIDU:
                $BaiduIP = new \Services\Location\GPS\Baidu();
                $result  = $BaiduIP->get($long, $lat);
                break;
            case self::GPS_DRIVER_AMAP:
                $AmapIP = new \Services\Location\GPS\Amap();
                $result = $AmapIP->get($long, $lat);
                break;
            default:
                Core::exception(STATUS_SERVER_ERROR, '定位驱动设置错误');
                break;
        }
        return $result;
    }
}