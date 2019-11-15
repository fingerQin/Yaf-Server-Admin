<?php
/**
 * 短信通道管理。
 * @author fingerQin
 * @date 2019-04-17
 */

namespace Services\Sms;

use Models\SmsConf;

class Channel extends \Services\AbstractBase
{
    /**
     * 获取全部短信通道。
     *
     * @return array
     */
    public static function all()
    {
        $SmsConfModel = new SmsConf();
        return $SmsConfModel->fetchAll(['id', 'title']);
    }

    /**
     * 以字典方式返回短信通道。
     *
     * @return array
     */
    public static function dict()
    {
        $tpls = self::all();
        $data = [];
        foreach ($tpls as $tpl) {
            $data[$tpl['id']] = $tpl['title'];
        }
        return $data;
    }
    
}