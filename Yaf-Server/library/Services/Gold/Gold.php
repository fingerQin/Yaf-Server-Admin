<?php
/**
 * 金币相关操作封装。
 * @author fingerQin
 * @date 2018-08-22
 */

namespace Services\Gold;

use finger\Validator;
use finger\Database\Db;
use Utils\YCore;
use Models\User;
use Models\GoldConsume;
use Models\Gold as GoldModel;

class Gold extends \Services\AbstractBase
{
    /**
     * 用户金币数量。
     *
     * @param  int  $userId  用户 ID。
     * @return int
     */
    public static function userGoldCount($userId)
    {
        $GoldModel = new GoldModel();
        $deta = $GoldModel->fetchOne([], ['userid' => $userId]);
        return $deta ? $deta['gold'] : 0;
    }

    /**
     * 金币消费[增加/扣除]。
     *
     * @param  int  $userId       用户ID。
     * @param  int  $gold         金币数量。
     * @param  int  $consumeType  消费类型。1增加、2扣减。
     * @param  int  $consumeCode  消费编码。
     * 
     * @return int 返回用户当前账户剩余金币。
     */
    public static function consume($userId, $gold, $consumeType, $consumeCode)
    {
        $GoldModel    = new GoldModel();
        $userGoldInfo = $GoldModel->fetchOne([], ['userid' => $userId]);
        $userGold     = 0; // 用户账户当前金币。
        if ($consumeType == GoldConsume::CONSUME_TYPE_ADD) {
            $userGold = self::add($userId, $gold);
        } else if ($consumeType == GoldConsume::CONSUME_TYPE_CUT) {
            $userGold = self::cut($userId, $gold);
        }
        $GoldConsumeModel = new GoldConsume();
        $datetime = date('Y-m-d H:i:s', time());
        $data = [
            'userid'       => $userId,
            'consume_type' => $consumeType,
            'consume_code' => $consumeCode,
            'gold'         => $gold,
            'u_time'       => $datetime,
            'c_time'       => $datetime
        ];
        $ok = $GoldConsumeModel->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
        return $userGold;
    }

    /**
     * 增加金币。
     *
     * @param  int  $userId  用户ID。
     * @param  int  $gold    金币数量。
     * 
     * @return int 返回添加之后用户账户的金币数量。
     */
    protected static function add($userId, $gold)
    {
        $GoldModel    = new GoldModel();
        $userGoldInfo = $GoldModel->fetchOne([], ['userid' => $userId]);
        $userGold     = 0; // 用户账户当前金币。
        if (empty($userGoldInfo)) {
            $data = [
                'userid' => $userId,
                'gold'   => $gold,
                'c_time' => date('Y-m-d H:i:s', time())
            ];
            $ok = $GoldModel->insert($data);
            if (!$ok) {
                YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
            }
            $userGold = $gold;
        } else {
            $data = [
                'v'      => $userGoldInfo['v'] + 1,
                'gold'   => $userGoldInfo['gold'] + $gold,
                'u_time' => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'userid' => $userId,
                'v'      => $userGoldInfo['v']
            ];
            $ok = $GoldModel->update($data, $where);
            if (!$ok) {
                YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
            }
            $userGold = $data['gold'];
        }
        return $userGold;
    }

    /**
     * 扣减金币。
     *
     * @param  int  $userId  用户ID。
     * @param  int  $gold    金币数量。
     * 
     * @return int 返回扣减之后用户账户的金币数量。
     */
    protected static function cut($userId, $gold)
    {
        $GoldModel    = new GoldModel();
        $userGoldInfo = $GoldModel->fetchOne([], ['userid' => $userId]);
        if (empty($userGoldInfo) || $userGoldInfo['gold'] < $gold) {
            YCore::exception(STATUS_SERVER_ERROR, '金币数量不足');
        }
        $data = [
            'v'      => $userGoldInfo['v'] + 1,
            'gold'   => $userGoldInfo['gold'] - $gold,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $where = [
            'userid' => $userId,
            'v'      => $userGoldInfo['v']
        ];
        $ok = $GoldModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
        return $data['gold'];
    }

    /**
     * 获取金币消费记录。
     *
     * @param  int     $userId       用户ID。
     * @param  int     $consumeType  消费类型：1增加、2扣减。
     * @param  string  $startTime    开始时间。
     * @param  string  $endTime      截止时间。
     * @param  int     $page         当前页码。
     * @param  int     $count        每页显示条数。
     * @return array
     */
    public static function records($userId = -1, $consumeType = -1, $startTime = '', $endTime = '', $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM finger_gold_consume ';
        $columns   = ' id, consume_type, consume_code, gold, c_time ';
        $where     = ' WHERE 1 ';
        $params    = [];
        if ($userId != -1) {
            $where .= ' AND userid = :userid ';
            $params[':userid'] = $userId;
        }
        if ($consumeType != -1) {
            $where .= ' AND consume_type = :consume_type ';
            $params[':consume_type'] = $consumeType;
        }
        if (strlen($startTime) > 0) {
            if (!Validator::is_date($startTime)) {
                YCore::exception(STATUS_SERVER_ERROR, '查询时间格式不正确');
            }
            $where .= ' AND c_time >= :start_time ';
            $params[':start_time'] = strtotime($startTime);
        }
        if (strlen($endTime) > 0) {
            if (!Validator::is_date($endTime)) {
                YCore::exception(STATUS_SERVER_ERROR, '查询时间格式不正确');
            }
            $where .= ' AND c_time <= :end_time ';
            $params[':end_time'] = strtotime($endTime);
        }
        $orderBy   = ' ORDER BY id DESC ';
        $sql       = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $item['c_time'] = date('n-d H:i', strtotime($item['c_time']));
            $item['title']  = GoldConsume::$consumeCodeLabels[$item['consume_code']] ?? '-';
            unset($item['consume_code']);
            $list[$key] = $item;
        }
        $result    = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }
}