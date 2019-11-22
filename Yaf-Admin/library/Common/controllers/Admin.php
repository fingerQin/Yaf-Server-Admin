<?php
/**
 * 管理后台公共controller。
 * --1、Yaf 框架会根据特有的类名后缀(Model、Controller、Plugin)进行自动加载。为避免这种情况请不要以这样的类名结尾。
 * --2、鉴于第一点，在 Yaf 框架内的所有类的加载请不要出现 Model、Controller、Plugin 等词出现在类名中。
 * --3、通过 Composer 加载的第三方包不受此影响。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common\controllers;

use finger\Utils\YCore;
use finger\Utils\YUrl;
use Services\Power\Auth;
use Services\System\OperationLog;

class Admin extends Common
{
    /**
     * 管理员ID。
     *
     * @var number
     */
    protected $adminId = 0;

    /**
     * 管理员真实姓名。
     *
     * @var string
     */
    protected $realname = '';

    /**
     * 管理员手机号码/账号。
     *
     * @var string
     */
    protected $mobile = '';

    /**
     * 管理员角色ID。
     *
     * @var number
     */
    protected $roleid = 0;

    /**
     * 前置方法
     * -- 1、登录权限判断。
     *
     * @see \common\controllers\Common::init()
     */
    public function init()
    {
        parent::init();
        try {
            $adminInfo      = Auth::checkAuth($this->_ctrlName, $this->_actionName);
            $this->adminId  = $adminInfo['adminid'];
            $this->realname = $adminInfo['real_name'];
            $this->mobile   = $adminInfo['mobile'];
            $this->roleid   = $adminInfo['roleid'];
            $this->writeAccessLog($this->adminId, $this->realname, $this->mobile, $this->_ctrlName, $this->_actionName);
        } catch (\Exception $e) {
            if ($e->getCode() == STATUS_LOGIN_TIMEOUT || $e->getCode() == STATUS_NOT_LOGIN || $e->getCode() == STATUS_OTHER_LOGIN) {
                if ($this->_request->isXmlHttpRequest()) {
                    YCore::exception($e->getCode(), $e->getMessage());
                } else {
                    $this->redirect(YUrl::createBackendUrl('Public', 'Login'));
                }
            } else {
                YCore::exception($e->getCode(), $e->getMessage());
            }
        }
        \Yaf_Registry::set('admin_user_roleid', $this->roleid);
    }

    /**
     * 记录管理后台访问日志(GET/POST)。
     * 
     * -- 只记录登录之后的操作日志。未登录的操作日志没有任何意义。
     *
     * @param  int     $adminid     管理员 ID。
     * @param  string  $realname    真实姓名。
     * @param  string  $mobile      手机账号。
     * @param  string  $ctrlName    控制器名称。
     * @param  string  $actionName  操作名称。
     *
     * @return void
     */
    private function writeAccessLog($adminid, $realname, $mobile, $ctrlName, $actionName)
    {
        $log = [
            'mobile'     => $mobile,
            'url'        => YUrl::getUrl(),
            'isAjax'     => $this->_request->isXmlHttpRequest() ? 1 : 0,
            'isPost'     => $this->_request->isPost() ? 1 : 0,
            'params'     => $this->_request->getPost()
        ];
        $ip = YCore::ip();
        if (strtolower($ctrlName) != 'public') {
            OperationLog::add($adminid, $realname, $ip, $ctrlName, $actionName, $log);
        }
    }
}