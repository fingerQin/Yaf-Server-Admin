<?php
/**
 * 管理员管理。
 * 
 * @author fingerQin
 * @date 2018-07-06
 */

namespace Services\Power;

use finger\Validator;
use finger\Database\Db;
use Utils\YString;
use Utils\YCore;
use Models\AdminUser as AdminUserModel;

class AdminUser extends \Services\AbstractBase
{
    /**
     * 获取管理员列表。
     * 
     * @param  string  $keyword  查询关键词(账号、手机、姓名)。
     * @param  int     $page     当前页码。
     * @param  int     $count    每页显示条数。
     * @return array
     */
    public static function list($keyword = '', $page, $count)
    {
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' a.adminid,a.real_name,a.passwd,a.mobile,a.roleid,'
                 . 'a.c_time,a.u_time,a.user_status,b.role_name ';
        $where   = ' WHERE a.user_status != :user_status ';
        $params  = [
            ':user_status' => AdminUserModel::STATUS_DELETED
        ];
        if (strlen($keyword) > 0) {
            $where .= ' AND ( a.real_name LIKE :real_name OR a.mobile LIKE :mobile )';
            $params[':real_name'] = "%{$keyword}%";
            $params[':mobile']    = "%{$keyword}%";
        }
        $orderBy    = ' ORDER BY a.adminid ASC ';
        $sql        = "SELECT COUNT(1) AS count FROM finger_admin_user AS a "
                    . "LEFT JOIN finger_admin_role AS b ON(a.roleid=b.roleid) {$where}";
        $countData  = Db::one($sql, $params);
        $total      = $countData ? $countData['count'] : 0;
        $sql        = "SELECT {$columns} FROM finger_admin_user AS a "
                    . "LEFT JOIN finger_admin_role AS b ON(a.roleid=b.roleid) "
                    . "{$where} {$orderBy} LIMIT {$offset},{$count}";
        $list       = Db::all($sql, $params);
        $result     = [
            'list'    => $list,
            'total'   => $total,
            'page'    => $page,
            'count'   => $count,
            'is_next' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 禁用/解禁账号。
     *
     * @param  int  $opAdminId  操作该功能的管理员。
     * @param  int  $adminId    被禁用/解禁的管理员账号。
     * @param  int  $status     状态。1-解禁/0-禁用。
     *
     * @return void
     */
    public static function forbid($opAdminId, $adminId, $status)
    {
        if ($adminId == 1) {
            YCore::exception(STATUS_SERVER_ERROR, '超级管理员不能操作');
        }
        $AdminUserModel  = new AdminUserModel();
        $adminUserDetail = $AdminUserModel->fetchOne([], ['adminid' => $adminId]);
        if (empty($adminUserDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '该管理员不存在');
        }
        if ($adminUserDetail['user_status'] == AdminUserModel::STATUS_DELETED) {
            YCore::exception(STATUS_SERVER_ERROR, '该管理已经被删除');
        }
        if ($status == AdminUserModel::STATUS_YES) {
            if ($adminUserDetail['user_status'] == AdminUserModel::STATUS_YES) {
                YCore::exception(STATUS_SERVER_ERROR, '该账号已经是正常登录状态');
            }
        } else {
            if ($adminUserDetail['user_status'] == AdminUserModel::STATUS_INVALID) {
                YCore::exception(STATUS_SERVER_ERROR, '该账号已经是禁用状态');
            }
        }
        $updata = [
            'user_status' => $status,
            'u_by'        => $opAdminId,
            'u_time'      => date('Y-m-d H:i:s', time())
        ];
        $status = $AdminUserModel->update($updata, ['adminid' => $adminId]);
        if (!$status) {
            YCore::exception(STATUS_SERVER_ERROR, '操作失败,请稍候重试');
        }
    }

    /**
     * 添加管理员。
     *
     * @param  int     $adminId      管理员ID。 
     * @param  string  $realname     真实姓名。
     * @param  string  $password     密码。
     * @param  string  $mobilephone  手机号码。
     * @param  int     $roleid       角色ID。
     *
     * @return void
     */
    public static function add($adminId, $realname, $password, $mobilephone, $roleid)
    {
        // [1]
        self::checkRealname($realname);
        self::checkPassword($password);
        self::checkMobilephone($mobilephone);
        self::isExistMobile($mobilephone, true);
        Role::isExist($roleid);

        $salt = YString::randomstr(6);
        $md5Password = Auth::encryptPassword($password, $salt);
        $data = [
            'real_name'   => $realname,
            'passwd'      => $md5Password,
            'mobile'      => $mobilephone,
            'passwd_salt' => $salt,
            'roleid'      => $roleid,
            'user_status' => AdminUserModel::STATUS_YES,
            'c_by'        => $adminId,
            'u_by'        => $adminId,
        ];
        $AdminUserModel = new AdminUserModel();
        $status         = $AdminUserModel->insert($data);
        if (!$status) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 编辑管理员。
     * 
     * @param  int     $opAdminId    当前操作此功能的管理员ID。
     * @param  int     $adminId      管理员ID。
     * @param  int     $realname     真实姓名。
     * @param  string  $password     密码。不填则保持原密码。
     * @param  string  $mobilephone  手机号码。
     * @param  int     $roleid       角色ID。
     * @return void
     */
    public static function edit($opAdminId, $adminId, $realname, $mobilephone, $roleid, $password = '')
    {
        // [1]
        self::checkRealname($realname);
        self::checkMobilephone($mobilephone);
        self::isExistMobile($mobilephone, false, $adminId);
        (strlen($password) > 0) && self::checkPassword($password);
        Role::isExist($roleid);

        $data = [
            'real_name' => $realname,
            'mobile'    => $mobilephone,
            'roleid'    => $roleid,
            'u_by'      => $opAdminId
        ];
        if (strlen($password) > 0) {
            $salt                = YString::randomstr(6);
            $md5Password         = Auth::encryptPassword($password, $salt);
            $data['passwd']      = $md5Password;
            $data['passwd_salt'] = $salt;
        }
        $where = [
            'adminid' => $adminId
        ];
        $AdminUserModel = new AdminUserModel();
        $status         = $AdminUserModel->update($data, $where);
        if (!$status) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除管理员账号。
     * 
     * -- 1、超级管理员账号是不允许删除的。
     *
     * @param  int  $opAdminId  操作管理员ID。
     * @param  int  $adminId    管理员账号ID。
     * @return void
     */
    public static function delete($opAdminId, $adminId)
    {
        if ($adminId == 1) {
            YCore::exception(STATUS_SERVER_ERROR, '超级管理员账号不能删除');
        }
        $data = [
            'user_status' => AdminUserModel::STATUS_DELETED,
            'u_time'      => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
            'u_by'        => $opAdminId
        ];
        $where = [
            'adminid' => $adminId
        ];
        $AdminUserModel  = new AdminUserModel();
        $ok = $AdminUserModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 修改密码。
     *
     * @param  int     $adminId  用户ID。
     * @param  string  $oldPwd   旧密码。
     * @param  string  $newPwd   新密码。
     * @return void
     */
    public static function editPwd($adminId, $oldPwd, $newPwd)
    {
        if (strlen($oldPwd) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '旧密码必须填写');
        }
        if (strlen($newPwd) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '新密码必须填写');
        }
        $AdminUserModel = new AdminUserModel();
        $adminInfo      = $AdminUserModel->fetchOne([], ['adminid' => $adminId]);
        if (empty($adminInfo) || $adminInfo['user_status'] != AdminUserModel::STATUS_YES) {
            YCore::exception(STATUS_SERVER_ERROR, '管理员不存在或已经删除');
        }
        if (!Validator::is_len($newPwd, 6, 20, true)) {
            YCore::exception(STATUS_SERVER_ERROR, '新密码长度必须6~20之间');
        }
        if (!Validator::is_alpha_dash($newPwd)) {
            YCore::exception(STATUS_SERVER_ERROR, '新密码格式不正确');
        }
        $oldPwdEncrypt = Auth::encryptPassword($oldPwd, $adminInfo['passwd_salt']);
        if ($oldPwdEncrypt != $adminInfo['passwd']) {
            YCore::exception(STATUS_SERVER_ERROR, '旧密码不正确!');
        }
        $salt = YString::randomstr(6);
        $encryptPassword = Auth::encryptPassword($newPwd, $salt);
        $data = [
            'passwd'      => $encryptPassword,
            'u_by'        => $adminId,
            'passwd_salt' => $salt
        ];
        $where = [
            'adminid' => $adminId
        ];
        $ok = $AdminUserModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '密码修改失败');
        }
    }

    /**
     * 获取管理员账号详情。
     * 
     * @param  int  $adminId  管理员账号ID。
     * @return array
     */
    public static function detail($adminId)
    {
        $AdminUserModel = new AdminUserModel();
        $adminDetail    = $AdminUserModel->fetchOne([], ['adminid' => $adminId]);
        if (empty($adminDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '管理员账号不存在或已经删除');
        }
        return $adminDetail;
    }

    /**
     * 获取管理员详情(不区分是否删除)。
     * 
     *
     * @param  int  $adminId  管理员ID。
     * @return array
     */
    public static function getAdminInfoSpecial($adminId)
    {
        $AdminUserModel = new AdminUserModel();
        $data = $AdminUserModel->fetchOne([], ['adminid' => $adminId]);
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '管理员不存在或已经删除');
        }
        return $data;
    }

    /**
     * 判断手机号码是否存在。
     * 
     * @param  string   $mobilephone    手机号码。
     * @param  bool     $isNewCreate    是否新创建的管理员。
     * @param  int      $adminId        如果是新创建的管理员。则管理ID是多少。
     * @return bool // true-存在、false-不存在。
     */
    protected static function isExistMobile($mobilephone, $isNewCreate = false, $adminId = 0)
    {
        $AdminUserModel = new AdminUserModel();
        $adminInfo      = $AdminUserModel->fetchOne([], ['mobile' => $mobilephone, 'user_status' => AdminUserModel::STATUS_YES]);
        if (!empty($adminInfo)) {
            if ($isNewCreate) {
                if ($adminInfo['adminid'] != $adminId) {
                    YCore::exception(STATUS_SERVER_ERROR, '手机号码已经存在');
                }
            }
        }
    }

    /**
     * 检查管理员账号格式。
     * 
     * @param  string  $username  管理员账号。
     * @return void
     */
    public static function checkUsername($username)
    {
        $data = [
            'username' => $username
        ];
        $rules = [
            'username' => '账号|require|alpha_dash|len:6:20:0'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查管理员密码格式。
     * 
     * @param  string  $password  密码。
     * @return void
     */
    public static function checkPassword($password)
    {
        $data = [
            'password' => $password
        ];
        $rules = [
            'password' => '密码|require|alpha_dash|len:6:20:0'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查手机号格式。
     * 
     * @param  string  $mobilephone  管理员手机号码。
     * @return void
     */
    public static function checkMobilephone($mobilephone)
    {
        $data = [
            'mobilephone' => $mobilephone
        ];
        $rules = [
            'mobilephone' => '手机号码|require|mobilephone'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查真实姓名格式。
     * 
     * @param  string  $realname  管理员真实姓名。
     * @return void
     */
    public static function checkRealname($realname)
    {
        $data = [
            'realname' => $realname
        ];
        $rules = [
            'realname' => '真实姓名|require|len:2:20:1'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查管理员账号是否存在。
     * 
     * @param  string  $username 管理员账号。
     * @param  string  $errMsg   自定义错误信息。
     * @return array
     */
    public static function isExistAdmin($username, $errMsg = '')
    {
        $AdminUserModel = new AdminUserModel();
        $adminDetail    = $AdminUserModel->fetchOne([], ['username' => $username, 'status' => AdminUser::STATUS_YES]);
        if (!empty($adminDetail)) {
            $errMsg = (strlen($errMsg) > 0) ? $errMsg : '管理员账号已经存在';
            YCore::exception(STATUS_SERVER_ERROR, $errMsg);
        }
        return $adminDetail;
    }
}