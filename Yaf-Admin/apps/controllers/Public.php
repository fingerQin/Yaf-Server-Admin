<?php
/**
 * 登录。
 * @author fingerQin
 * @date 2018-07-06
 */

use Services\Power\Auth;
use Services\Sms\Sms;

class PublicController extends \Common\controllers\Guest
{
    /**
     * 登录。
     */
    public function loginAction()
    {
        if ($this->_request->isPost()) {
            $username = $this->getString('username', '');
            $password = $this->getString('password', '');
            $code     = $this->getString('code', '');
            Auth::login($username, $password, $code);
            $this->json(true, '登录成功');
        }
    }

    /**
     * 登出。
     */
    public function logoutAction() 
    {
        Auth::logout();
        $this->json(true, '退出成功');
    }

    /**
     * 获取登录短信验证码
     */
    public function getSmsAction()
    {
        $mobile = $this->getString('username', '');
        Sms::sendAdminLogin($mobile);
        $this->json(true, '短信已发送');
    }
}