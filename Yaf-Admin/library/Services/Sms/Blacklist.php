<?php
/**
 * 黑名单管理。
 * @author fingerQin
 * @date 2019-05-23
 */

namespace Services\Sms;

use finger\Cache;
use finger\Core;
use Models\SmsBlacklist;
use finger\Validator;
use finger\Database\Db;

class Blacklist extends \Services\AbstractBase
{
    /**
     * 手机号码黑名单缓存 KEY。
     */
    const BLACKLIST_MOBILE_CACHE_KEY = 'sms-blacklist-mobile';

    /**
     * 黑名单列表。
     * 
     * @param  string  $mobile  手机号码。
     * @param  int     $page    页码。
     * @param  int     $count   每页显示条数。
     * 
     * @return array
     */
    public static function lists($mobile, $page = 1, $count = 20)
    {
        if (strlen($mobile) > 0 && !Validator::is_mobilephone($mobile)) {
            Core::exception(STATUS_SERVER_ERROR, '手机号格式不正确');
        }
        $from    = ' FROM finger_sms_blacklist ';
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' * ';
        $where   = ' WHERE 1 ';
        $params  = [];
        if (strlen($mobile) > 0) {
            $where .= ' AND mobile = :mobile ';
            $params[':mobile'] = $mobile;
        }
        $orderBy   = ' ORDER BY id DESC ';
        $sql       = "SELECT COUNT(1) AS count {$from} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$from} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        $result    = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 清除黑名单缓存。
     *
     * @return void
     */
    public static function clearCache()
    {
        $redis = Cache::getRedisClient();
        $redis->del(self::BLACKLIST_MOBILE_CACHE_KEY);
    }

    /**
     * 添加手机号。
     *
     * @param  string  $mobiles  手机号。
     *
     * @return void
     */
    public static function add($mobiles)
    {
        $mobiles = str_replace(' ', '', $mobiles);
        $mobiles = explode("\r\n", $mobiles);
        $mobiles = array_unique($mobiles);
        if (empty($mobiles)) {
            Core::exception(STATUS_SERVER_ERROR, '没有添加任何手机号码');
        }
        $datetime = date('Y-m-d H:i:s', TIMESTAMP);
        Db::beginTransaction();
        foreach ($mobiles as $mobile) {
            if (!Validator::is_mobilephone($mobile)) {
                continue;
            }
            $sql  = 'REPLACE INTO finger_sms_blacklist (mobile, c_time) VALUES(:mobile, :c_time)';
            $data = [
                ':mobile' => $mobile,
                ':c_time' => $datetime
            ];
            Db::execute($sql, $data);
        }
        Db::commit();
    }

    /**
     * 删除黑名单。
     *
     * @param  int  $id  用户 ID。
     *
     * @return void
     */
    public static function delete($id)
    {
        $SmsBlacklistModel = new SmsBlacklist();
        $ok = $SmsBlacklistModel->delete(['id' => $id]);
        if (!$ok) {
            Core::exception(STATUS_SERVER_ERROR, '删除失败');
        }
        self::clearCache();
    }
}