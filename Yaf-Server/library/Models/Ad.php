<?php
/**
 * 广告表 Model。
 * @author fingerQin
 * @date 2018-08-20
 */

namespace Models;

class Ad extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_ad';

    protected $primaryKey = 'ad_id';

    /**
     * 限制条件位值定义。
     */
    const FLAG_LOGIN_YES        = 1;    // 已登录。
    const FLAG_LOGIN_NO         = 2;    // 未登录。
    const FLAG_REGISTER_MONTH   = 4;    // 已注册 30 天。
    const FLAG_EXCHANGE_NO      = 8;    // 未兑换过商品。
    const FLAG_REALNAME_YES     = 16;   // 已实名。
    const FLAG_REALNAME_NO      = 32;   // 未实名。
    const FLAG_INVITE_YES       = 64;   // 已成功邀请。
    const FLAG_INVITE_NO        = 128;  // 未成功邀请。

    /**
     * 位值 FLAG 字典。
     *
     * @var array
     */
    public static $flagDict = [
        self::FLAG_LOGIN_YES        => '已登录',
        self::FLAG_LOGIN_NO         => '未登录',
        self::FLAG_REGISTER_MONTH   => '注册时长30天',
        self::FLAG_EXCHANGE_NO      => '未兑换过',
        self::FLAG_REALNAME_YES     => '已实名',
        self::FLAG_REALNAME_NO      => '未实名',
        self::FLAG_INVITE_YES       => '已成功邀请',
        self::FLAG_INVITE_NO        => '未成功邀请'
    ];
}