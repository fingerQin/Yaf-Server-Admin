<?php
/**
 * 未授权 Appid 监控上报数据消费。
 * @author fingerQin
 * @date 2019-06-13
 */

namespace Services\Monitor\Sub;

class Unauthorized extends \Services\Monitor\AbstractBase
{
    /**
     * 运行真实的业务。
     * 
     * @param array $data 监控上报数据。
     *
     * @return void
     */
    public static function runService($data)
    {
        
    }
}