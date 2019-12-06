<?php
/**
 * 用户数据相关操作。
 * @author fingerQin
 * @date 2018-08-14
 */

namespace Services\User;

use finger\Cache;
use finger\Core;
use finger\Validator;
use finger\Database\Db;
use finger\Date;
use finger\Strings;
use Models\User as UserModel;

class User extends \Services\AbstractBase
{    
    /**
     * 获取用户列表。
     *
     * @param  string  $mobile     手机账号。
     * @param  string  $starttime  开始注册时间。
     * @param  string  $endtime    截止注册时间。
     * @param  int     $page       当前页码。
     * @param  int     $count      每页显示条数。
     * @return array
     */
    public static function list($mobile = '', $starttime = '', $endtime = '', $page = 1, $count = 20)
    {
        if (strlen($starttime) > 0 && !Validator::is_date($starttime)) {
            Core::exception(STATUS_SERVER_ERROR, '开始注册时间格式不对');
        }
        if (strlen($endtime) > 0 && !Validator::is_date($endtime)) {
            Core::exception(STATUS_SERVER_ERROR, '截止注册时间格式不对');
        }
        $from    = ' FROM finger_user ';
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' * ';
        $where   = ' WHERE 1 ';
        $params  = [];
        if (strlen($mobile) > 0) {
            $where .= ' AND mobile = :mobile ';
            $params[':mobile'] = $mobile;
        }
        if (strlen($starttime) > 0) {
            $where .= ' AND c_time > :starttime ';
            $params[':starttime'] = $starttime;
        }
        if (strlen($endtime) > 0) {
            $where .= ' AND c_time < :endtime ';
            $params[':endtime'] = $endtime;
        }
        $orderBy   = ' ORDER BY userid DESC ';
        $sql       = "SELECT COUNT(1) AS count {$from} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$from} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        foreach ($list as $k => $val) {
            $val['c_time']          = Date::formatDateTime($val['c_time']);
            $val['last_login_time'] = Date::formatDateTime($val['last_login_time']);
            $val['platform']        = self::$platformLabel[$val['platform']];
            $val['cur_status']      = UserModel::$statusLabel[$val['cur_status']];
            $list[$k]               = $val;
        }
        $result = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 用户详情信息。
     *
     * @param  int  $userid  用户ID。
     * @return array
     */
    public static function getUserDetail($userid)
    {
        $sql = 'SELECT a.user_id,a.username,a.mobilephone,a.mobilephone_ok,a.mobilephone_time,'
             . 'a.email,a.email_ok,a.email_time,a.created_time,b.realname,b.avatar,b.signature, '
             . 'b.birthday, b.sex, a.last_login_time '
             . 'FROM ms_user AS a LEFT JOIN ms_user_data AS b ON(a.user_id=b.user_id) '
             . 'WHERE a.user_id = :user_id';
        $params = [
            ':user_id' => $userid
        ];
        $userinfo = Db::one($sql, $params);
        if (empty($userinfo)) {
            Core::exception(STATUS_SERVER_ERROR, '用户不存在或已经删除');
        }
        return $userinfo;
    }

    /**
     * 修改密码。
     *
     * @param  int     $userid    用户ID。
     * @param  string  $password  密码。
     * @return bool
     */
    public static function editPwd($userid, $password)
    {
        if (strlen($password) === 0) {
            Core::exception(STATUS_ERROR, '密码必须填写');
        }
        if (!Validator::is_alpha_dash($password)) {
            Core::exception(STATUS_ERROR, '新密码格式不正确');
        }
        if (!Validator::is_len($password, 6, 20)) {
            Core::exception(STATUS_ERROR, '新密码长度必须6~20位之间');
        }
        $UserModel = new UserModel();
        $salt      = Strings::randomstr(6);
        $password  = self::encryptPassword($password, $salt);
        $updata    = [
            'salt'   => $salt,
            'pwd'    => $password,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $ok = $UserModel->update($updata, ['userid' => $userid]);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '密码修改失败');
        }
        return true;
    }

     /**
     * 修改密码。
     *
     * @param  int  $userid  用户ID。
     * @param  int  $status  密码。
     * @return bool
     */
    public static function editStatus($userid, $status)
    {
        if (!array_key_exists($status, UserModel::$statusLabel)) {
            Core::exception(STATUS_SERVER_ERROR, '状态设置不正确');
        }
        $UserModel = new UserModel();
        $updata    = [
            'cur_status' => $status,
            'u_time'     => date('Y-m-d H:i:s', time())
        ];
        $ok = $UserModel->update($updata, ['userid' => $userid]);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '密码修改失败');
        }
        return true;
    }

    /**
     * 加密密码。
     *
     * @param  string  $password  密码明文。
     * @param  string  $salt      密码加密盐。
     * @return string
     */
    public static function encryptPassword($password, $salt)
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 清除账号登录错误锁定。
     *
     * @param  int  $userid  用户 ID。
     *
     * @return void
     */
    public static function clearAccountLoginErrorLock($userid)
    {
        $userinfo = (new UserModel())->fetchOne([], ['userid' => $userid]);
        if (empty($userinfo)) {
            Core::exception(STATUS_SERVER_ERROR, '账号不存在');
        }
        $redis = Cache::getRedisClient();
        $errCounterKey  = "login_account_lock_{$userinfo['mobile']}";
        $errDeadlineKey = "login_account_unlock_date_{$userinfo['mobile']}";
        $redis->del($errCounterKey);
        $redis->del($errDeadlineKey);
    }
}