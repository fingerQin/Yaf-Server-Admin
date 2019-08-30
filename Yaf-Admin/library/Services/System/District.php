<?php
/**
 * 地区操作业务封装。
 * @author fingerQin
 * @date 2019-08-29
 */

namespace Services\System;

use Models\District as ModelsDistrict;

class District extends \Services\AbstractBase
{
    /**
     * 根据区县编码获取地区信息。
     *
     * @param  string  $districtCode  地区编码。
     *
     * @return array
     */
    public static function getDetailByDistrictCode($districtCode)
    {
        $columns = ['province_name', 'city_name', 'district_name'];
        $DistrictModel  = new ModelsDistrict();
        return $DistrictModel->fetchOne($columns, ['district_code' => $districtCode]);
    }
}