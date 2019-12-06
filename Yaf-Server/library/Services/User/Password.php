<?php
/**
 * 密码操作相关业务封装。
 * @author fingerQin
 * @date 2018-08-15
 */

namespace Services\User;

use finger\Core;
use finger\Strings;
use finger\Validator;
use Models\User;
use Services\Sms\Sms;

class Password extends \Services\AbstractBase
{
    /**
     * 修改密码。
     *
     * @param  int     $userid  用户ID。
     * @param  string  $oldPwd  旧密码。
     * @param  string  $newPwd  新密码。
     * @return bool
     */
    public static function edit($userid, $oldPwd, $newPwd)
    {
        if (strlen($oldPwd) === 0) {
            Core::exception(STATUS_ERROR, '原密码必须填写');
        }
        if (strlen($newPwd) === 0) {
            Core::exception(STATUS_ERROR, '新密码必须填写');
        }
        if ($oldPwd == $newPwd) {
            Core::exception(STATUS_ERROR, '新密码不能与原密码相同');
        }
        Auth::checkPassword($newPwd);
        $UserModel = new User();
        $userinfo  = $UserModel->fetchOne([], ['userid' => $userid]);
        $password  = Auth::encryptPwd($oldPwd, $userinfo['salt']);
        if ($password != $userinfo['pwd']) {
            Core::exception(STATUS_ERROR, '原密码不正确');
        }
        $salt     = Strings::randomstr(6);
        $password = Auth::encryptPwd($newPwd, $salt);
        $updata   = [
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
     * 找回密码。
     *
     * @param  string  $mobile  手机账号。
     * @param  string  $code    验证码。
     * @param  string  $newPwd  新密码。
     * @return array
     */
    public static function find($mobile, $code, $newPwd)
    {
        Validator::is_mobilephone($mobile);
        Auth::checkCaptcha($code);
        Auth::checkPassword($newPwd);
        Sms::verify($mobile, $code, 'USER_FIND_PWD');
        $salt     = Strings::randomstr(6);
        $password = Auth::encryptPwd($newPwd, $salt);
        $updata   = [
            'pwd'    => $password,
            'salt'   => $salt,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $UserModel = new User();
        $ok = $UserModel->update($updata, ['mobile' => $mobile]);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }
}