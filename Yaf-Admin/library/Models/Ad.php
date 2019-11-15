<?php
/**
 * 广告表。
 * @author fingerQin
 * @date 2018-08-07
 */

namespace Models;

use finger\Database\Db;

class Ad extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'finger_ad';

    /**
     * 终端。
     */
    const TERMINAL_ANDROID      = 1;    // Android App。
    const TERMINAL_IOS          = 2;    // iOS App。
    const TERMINAL_PC           = 4;    // PC 网站。
    const TERMINAL_H5           = 8;    // H5 触屏端。
    const TERMINAL_MINI_PROGRAM = 16;   // 微信小程序。

    /**
     * 终端字典。
     *
     * @var array
     */
    public static $terminalDict = [
        self::TERMINAL_ANDROID      => 'Android',
        self::TERMINAL_IOS          => 'iOS',
        self::TERMINAL_H5           => 'H5',
        self::TERMINAL_PC           => 'PC',
        self::TERMINAL_MINI_PROGRAM => '小程序'
    ];

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

    /**
     * 翻译终端为中文显示。
     *
     * @param  int  $terminal
     * @return void
     */
    protected static function translateTerminalToLabel($terminal)
    {
        $result = [];
        foreach (self::$terminalDict as $_terminal => $tm) {
            if (($terminal & $_terminal) == $_terminal) {
                $result[] = $tm;
            }
        }
        return implode('<br/>', $result);
    }

    /**
     * 获取指定广告位置的广告列表。
     *
     * @param  int     $posId    广告位置ID。
     * @param  string  $adName   广告名称。模糊搜索广告名称。
     * @param  string  $display  显示状态：-1全部、1显示、0隐藏。
     * @param  int     $page     页码。
     * @param  int     $count    每页显示条数。
     * @return array
     */
    public function getList($posId, $adName = '', $display = -1, $page = 1, $count = 20)
    {
        $offset  = $this->getPaginationOffset($page, $count);
        $columns = ' * ';
        $where   = ' WHERE status = :status AND pos_id = :pos_id ';
        $params  = [
            ':status' => self::STATUS_YES,
            ':pos_id' => $posId
        ];
        if (strlen($adName) > 0) {
            $where .= ' AND ad_name LIKE :ad_name ';
            $params[':ad_name'] = "%{$adName}%";
        }
        if ($display != -1) {
            $where .= ' AND display LIKE :display ';
            $params[':display'] = $display;
        }
        $orderBy   = ' ORDER BY listorder ASC, ad_id DESC ';
        $sql       = "SELECT COUNT(1) AS count FROM {$this->tableName} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} FROM {$this->tableName} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        foreach ($list as $k => $v) {
            $list[$k]['terminal_label'] = self::translateTerminalToLabel($v['terminal']);
        }
        $result = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => $this->isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 设置广告排序值。
     *
     * @param  int    $adId     广告ID。
     * @param  array  $sortVal  排序值。
     * @return bool
     */
    public function sortAd($adId, $sortVal)
    {
        $data = [
            'listorder' => $sortVal
        ];
        $where = [
            'ad_id' => $adId
        ];
        return $this->update($data, $where);
    }
}