<?php
/**
 * 权限相关。
 * 
 * @author fingerQin
 * @date 2018-07-06
 */

namespace Services\Power;

use Utils\YUrl;
use Models\AdminUser;
use Utils\YCore;
use Utils\YCache;
use Services\Sms\Sms;

class Auth extends \Services\AbstractBase
{
    /**
     * 管理员登录。
     *
     * @param  string  $username  账号。
     * @param  string  $password  密码。
     * @param  int     $code      登录短信验证码
     * @return void
     */
    public static function login($username, $password, $code)
    {
        if (strlen($username) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '账号不能为空');
        }
        if (strlen($password) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '密码不能为空');
        }

        if (strlen($code) == 0) {
            YCore::exception(STATUS_SERVER_ERROR, '登录短信验证码不能为空');
        } elseif (!preg_match('/^\d+$/', $code)) {
            YCore::exception(STATUS_SERVER_ERROR, '请输入正确的登录短信验证码');
        }

        $AdminUserModel = new AdminUser();
        $adminInfo      = $AdminUserModel->fetchOne([], [
            'mobile'      => $username,
            'user_status' => AdminUser::STATUS_YES
        ]);
        if (empty($adminInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '账号不存在');
        }
        $encryptPwd = self::encryptPassword($password, $adminInfo['passwd_salt']);
        if ($encryptPwd != $adminInfo['passwd']) {
            YCore::exception(STATUS_SERVER_ERROR, '密码不正确');
        }
        // 验证登录手机验证码
        Sms::verify($username, 'ADMIN_LOGIN_CODE', $code);
        $authToken = self::createToken($adminInfo['adminid'], $encryptPwd);
        self::setAuthToken($adminInfo['adminid'], $authToken, $_SERVER['REQUEST_TIME']);
        $adminCookieDomain = YUrl::getDomainName(false);
        setcookie('admin_token', $authToken, 0, '/', $adminCookieDomain);
    }

    /**
     * 检查用户权限。
     * 
     * -- 1、在每次用户访问程序的时候调用。
     *
     * @param  string  $moduleName  模块名称。
     * @param  string  $ctrlName    控制器名称。
     * @param  string  $actionName  操作名称。
     * @return array 基本信息。
     */
    public static function checkAuth($ctrlName, $actionName)
    {
        // [1] token解析
        $token       = isset($_COOKIE['admin_token']) ? $_COOKIE['admin_token'] : '';
        $tokenParams = self::parseToken($token);
        $adminId     = $tokenParams['adminid'];
        $password    = $tokenParams['password'];
        // [2] 用户存在与否判断
        $AdminUsermodel = new AdminUser();
        $adminInfo      = $AdminUsermodel->fetchOne([], [
            'adminid'     => $adminId,
            'user_status' => AdminUser::STATUS_YES
        ]);
        if (empty($adminInfo)) {
            self::logout();
            YCore::exception(STATUS_SERVER_ERROR, '账号不存在或已经被禁用');
        }
        if ($password != $adminInfo['passwd']) {
            self::logout();
            YCore::exception(STATUS_SERVER_ERROR, '您的密码被修改,请重新登录');
        }
        // [3] token 是否赶出了超时时限
        $cacheKeyToken = "admin_token_key_{$adminId}";
        $cacheToken    = YCache::get($cacheKeyToken);
        if ($cacheToken === false) {
            self::logout();
            YCore::exception(STATUS_NOT_LOGIN, '您还没有登录');
        }
        if ($cacheToken === null) {
            self::logout();
            YCore::exception(STATUS_LOGIN_TIMEOUT, '登录超时,请重新登录');
        }
        if ($cacheToken != $token) {
            self::logout();
            YCore::exception(STATUS_OTHER_LOGIN, '您的账号已在其他地方登录');
        }
        // [4] 默认的 IndexController 不进行角色权限验证。
        if (strtolower($ctrlName) != 'index') {
            $ok = Menu::checkMenuPower($adminInfo['roleid'], $ctrlName, $actionName);
            if (!$ok) {
                YCore::exception(STATUS_SERVER_ERROR, '您没有权限执行此操作');
            }
        }
        self::setAuthToken($adminId, $token);
        $data = [
            'adminid'   => $adminInfo['adminid'],
            'real_name' => $adminInfo['real_name'],
            'mobile'    => $adminInfo['mobile'],
            'roleid'    => $adminInfo['roleid']
        ];
        return $data;
    }

    /**
     * 退出登录。
     * 
     * @return void
     */
    public static function logout()
    {
        $adminCookieDomain = YUrl::getDomainName(false);
        $validTime = $_SERVER['REQUEST_TIME'] - 3600;
        setcookie('admin_token', '', $validTime, '/', $adminCookieDomain);
    }

    /**
     * 设置 authToken 最后的访问时间。
     * 
     * @param  int     $adminId    管理员ID。
     * @param  string  $authToken  auth_token。
     * @return void
     */
    private static function setAuthToken($adminId, $authToken)
    {
        $cacheKey = "admin_token_key_{$adminId}";
        YCache::set($cacheKey, $authToken, 3600);
    }

    /**
     * 解析Token。
     *
     * @param  string  $token  token 会话。
     * @return array
     */
    private static function parseToken($token)
    {
        $data = YCore::sys_auth($token, 'DECODE');
        $data = explode("\t", $data);
        if (count($data) != 2) {
            YCore::exception(STATUS_LOGIN_TIMEOUT, '登录超时,请重新登录');
        }
        $result = [
            'adminid'  => $data[0], // 用户ID。
            'password' => $data[1]  // 加密的密码。
        ];
        return $result;
    }

    /**
     * 生成 Token。
     * 
     * @param  int     $adminId   管理员ID。
     * @param  string  $password  用户表 password 字段。
     * @return string
     */
    private static function createToken($adminId, $password)
    {
        $str = "{$adminId}\t{$password}";
        return YCore::sys_auth($str, 'ENCODE', '', 0);
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
}