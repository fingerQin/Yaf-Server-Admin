<?php
/**
 * 用户相关业务接口。
 * 
 * @author fingerQin
 * @date 2018-08-15
 */

namespace Services\User;

use finger\Utils\YCore;
use Services\Sms\Sms;

class User extends \Services\AbstractBase
{
    /**
     * 获取用户详情。
     * 
     * -- 后续会整合其他信息合并进来。
     *
     * @param  int  $userid  用户 ID。
     * @return array
     */
    public static function detail($userid)
    {
        $columns   = 'mobile, open_id, nickname, headimg, intro, c_time AS reg_time';
        $UserModel = new \Models\User();
        return $UserModel->fetchOne($columns, ['userid' => $userid]);
    }

    /**
     * 更改手机号。
     * 
     * @param  int     $userid    用户 ID。
     * @param  string  $password  账号密码。
     * @param  string  $mobile    手机号码。
     * @param  string  $code      验证码。
     * 
     * @return void
     */
    public static function changeMobile($userid, $password, $mobile, $code)
    {
        $UserModel = new \Models\User();
        $userinfo  = $UserModel->fetchOne(['mobile', 'salt', 'pwd'], ['userid' => $userid]);
        if ($userinfo['mobile'] == $mobile) {
            YCore::exception(STATUS_SERVER_ERROR, '手机号未变化');
        }
        $detail = $UserModel->fetchOne(['userid'], ['mobile' => $mobile]);
        if (!empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '该手机号已经被占用');
        }
        $md5Pwd = Auth::encryptPwd($password, $userinfo['salt']);
        if ($md5Pwd != $userinfo['pwd']) {
            YCore::exception(STATUS_SERVER_ERROR, '密码不正确');
        }
        Sms::verify($mobile, $code, 'CHANGE_MOBILE_OLD');
        $updata = [
            'mobile' => $mobile,
            'u_time' => date('Y-m-d H:i:s')
        ];
        $status = $UserModel->update($updata, ['userid' => $userid]);
        if (!$status) {
            YCore::exception(STATUS_SERVER_ERROR, '手机号更改失败');
        }
    }
}