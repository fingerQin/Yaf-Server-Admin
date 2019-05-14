<?php
/**
 * 公用异常处理。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common\controllers;

use finger\ServiceException;
use Utils\YCore;
use Utils\YLog;

class Error extends \Common\controllers\Common
{
    private $errMsgTpl = [
        STATUS_FORBIDDEN        => '您没有权限访问',
        STATUS_NOT_FOUND        => '您访问的资源不存在',
        STATUS_SERVER_ERROR     => '服务器发生一个问题',
        STATUS_ERROR            => '服务器繁忙,请稍候重试',
        STATUS_LOGIN_TIMEOUT    => '登录超时,请重新登录',
        STATUS_NOT_LOGIN        => '您还未登录',
        STATUS_OTHER_LOGIN      => '您的账号在其他地方登录',
        STATUS_ALREADY_REGISTER => '您的账号已经注册',
        STATUS_UNREGISTERD      => '您的账号还未注册',
        STATUS_PASSWORD_EDIT    => '您的密码已经修改,请重新登录'
    ];

    /**
     * 也可通过$request->getException()获取到发生的异常
     */
    public function errorAction($exception)
    {
        $errCode = $exception->getCode();
        $errMsg  = $exception->getMessage();

        // [1] 参数验证错误
        if ($exception instanceof ServiceException && isset($this->errMsgTpl[$errCode])) {
            if (YCore::appconfig('app.debug')) { // 调试模式会输出具体的错误。
                $errMsg = ($errCode != STATUS_ERROR) ? $errMsg : $exception->__toString();
            }
            if ($errCode == STATUS_ERROR) {
                YLog::log($exception->log(), 'errors', 'log');
            } else {
                YLog::log($exception->log(), 'serviceErr', 'log');
            }
        } else {
            $errCode = STATUS_ERROR;
            $errMsg  = $this->errMsgTpl[$errCode];
            if (YCore::appconfig('app.debug')) { // 调试模式会输出具体的错误。
                $errMsg = $exception->__toString();
            }
            YLog::log($exception->log(), 'errors', 'log');
        }

        // [2] 根据是不同的请求类型响应不的数据。
        if (defined('IS_API')) {
            $data = [
                'code' => (int)$errCode,
                'msg'  => $errMsg
            ];
            YLog::writeApiResponseLog($data);
            echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            $this->end();
        } else if ($this->_request->isCli()) {
            echo $exception->__toString();
            $this->end();
        } else {
            $this->error("{$errMsg}", '', 0);
        }
        exit(0);
    }
}